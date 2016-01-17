<?php

namespace FireflyIII\Helpers\Csv\Specifix;

use Log;

/**
 * Parses the description from txt files for ABN AMRO bank accounts. 
 * 
 * Based on the logic as described in the following Gist:
 * https://gist.github.com/vDorst/68d555a6a90f62fec004
 *
 * @package FireflyIII\Helpers\Csv\Specifix
 */
class AbnAmroDescription
{
    /** @var array */
    protected $data;

    /** @var array */
    protected $row;


    /**
     * @return array
     */
    public function fix()
    {
        $this->handleAmount();
        
        // Try to parse the description in known formats.
        $parsed = $this->parseSepaDescription() || $this->parseTRTPDescription() || $this->parseGEABEADescription() || $this->parseABNAMRODescription();
        
        // If the description could not be parsed, specify an unknown opposing account, as an opposing account is required
        if( !$parsed ) {
            $this->data[ "opposing-account-name" ] = trans('unknown');
        }
        
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param array $row
     */
    public function setRow($row)
    {
        $this->row = $row;
    }
    
    protected function handleAmount() {
        $this->data['amount'] = floatval(str_replace(',', '.', $this->row[6]));
    }

    /**
     * Parses the current description in SEPA format
     * @return boolean true if the description is SEPA format, false otherwise
     */
    protected function parseSepaDescription()
    {
        // See if the current description is formatted as a SEPA plain description
        if( preg_match( "/^SEPA(.{28})/", $this->data[ "description" ], $matches ) ) {
            Log::debug('AbnAmroSpecifix: Description is structured as SEPA plain description.');
            $type = trim($matches[1]);
            
            // SEPA plain descriptions contain several key-value pairs, split by a colon
            preg_match_all( "/([A-Za-z]+(?=:\s)):\s([A-Za-z 0-9.-]+(?=\s))/", $this->data[ "description" ], $matches, PREG_SET_ORDER );
            
            foreach( $matches as $match ) {
                $key = $match[1];
                $value = trim($match[2]);
                
                switch( strtoupper($key) ) {
                    case 'OMSCHRIJVING':
                        $this->data['description'] = $value;
                        break;
                    case 'NAAM':
                        $this->data['opposing-account-name'] = $value;
                        break;
                    case 'IBAN':
                        $this->data['opposing-account-iban'] = $value;
                        break;
                    default:
                        // Ignore the rest
                }
            }
            
            // Add the type to the description
            if( $type ) 
                $this->data['description'] .= ' (' . $type . ')';
            
            return true;
        }
        
        return false;
    }

    /**
     * Parses the current description in TRTP format
     * @return boolean true if the description is TRTP format, false otherwise
     */
    protected function parseTRTPDescription()
    {
        // See if the current description is formatted in TRTP format
        if( preg_match_all( "!\/([A-Z]{3,4})\/([^/]*)!", $this->data[ "description" ], $matches, PREG_SET_ORDER ) ) {
            Log::debug('AbnAmroSpecifix: Description is structured as TRTP format.');
    
            foreach( $matches as $match ) {
                $key = $match[1];
                $value = trim($match[2]);
                
                switch( strtoupper($key) ) {
                    case 'TRTP':
                        $type = $value;
                        break;
                    case 'NAME':
                        $this->data['opposing-account-name'] = $value;
                        break;
                    case 'REMI':
                        $this->data['description'] = $value;
                        break;
                    case 'IBAN':
                        $this->data['opposing-account-iban'] = $value;
                        break;
                    default:
                        // Ignore the rest
                }
            }
            
            // Add the type to the description
            if( $type )
                $this->data['description'] .= ' (' . $type . ')';
    
            return true;
        }
    
        return false;
    }

    /**
     * Parses the current description in GEA/BEA format
     * @return boolean true if the description is GEA/BEAformat, false otherwise
     */
    protected function parseGEABEADescription()
    {
        // See if the current description is formatted in GEA/BEA format
        if( preg_match( "/([BG]EA) +(NR:[a-zA-Z:0-9]+) +([0-9.\/]+) +([^,]*)/", $this->data[ "description" ], $matches ) ) {
            Log::debug('AbnAmroSpecifix: Description is structured as GEA or BEA format.');
    
            $this->data[ "opposing-account-name" ] = $matches[4];
            $this->data[ "description" ] = $matches[4] . " (" . $matches[1] . ")";
        }
    
        return false;
    }
    
    /**
     * Parses the current description with costs from ABN AMRO itself
     * @return boolean true if the description is GEA/BEAformat, false otherwise
     */
    protected function parseABNAMRODescription()
    {
        // See if the current description is formatted in ABN AMRO format
        if( preg_match( "/ABN AMRO.{24} (.*)/", $this->data[ "description" ], $matches ) ) {
            Log::debug('AbnAmroSpecifix: Description is structured as costs from ABN AMRO itself.');
    
            $this->data[ "opposing-account-name" ] = "ABN AMRO";
            $this->data[ "description" ] = $matches[1];
        }
    
        return false;
    }
    
}

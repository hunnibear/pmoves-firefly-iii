<?php
namespace FireflyIII\Helpers\Csv\Specifix;

/**
 * Interface SpecifixInterface
 *
 * @package FireflyIII\Helpers\Csv\Specifix
 */
interface SpecifixInterface
{
    const PRE_PROCESSOR  = 1;
    const POST_PROCESSOR = 2;

    /**
     * Implement bank and locale related fixes.
     */
    public function fix();

    /**
     * @return int
     */
    public function getProcessorType();

    /**
     * @param array $data
     */
    public function setData($data);

    /**
     * @param int $processorType
     *
     * @return $this
     */
    public function setProcessorType($processorType);

    /**
     * @param array $row
     */
    public function setRow($row);
}

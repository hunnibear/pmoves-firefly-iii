<?php


/*
 * QueryParser.php
 * Copyright (c) 2025 https://github.com/Sobuno
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see https://www.gnu.org/licenses/.
 */

declare(strict_types=1);

namespace FireflyIII\Support\Search\QueryParser;

/**
 * Represents a result from parsing a query node
 *
 * Contains the parsed node and a flag indicating if this is the end of the query.
 * Used to handle subquery parsing and termination.
 */
class NodeResult
{
    public function __construct(
        public readonly ?Node $node,
        public readonly bool $isSubqueryEnd
    ) {
    }
}


/**
 * Single-pass parser that processes query strings into structured nodes.
 * Scans each character once (O(n)) to build field searches, quoted strings,
 * prohibited terms and nested subqueries without backtracking.
 */
class QueryParser implements QueryParserInterface
{
    private string $query;
    private int $position = 0;

    /** @return NodeGroup */
    public function parse(string $query): NodeGroup
    {
        $this->query = $query;
        $this->position = 0;
        return $this->buildNodeGroup(false);
    }

    /** @return NodeGroup */
    private function buildNodeGroup(bool $isSubquery, bool $prohibited = false): NodeGroup
    {
        $nodes = [];
        $nodeResult = $this->buildNextNode($isSubquery);

        while ($nodeResult->node !== null) {
            $nodes[] = $nodeResult->node;
            if($nodeResult->isSubqueryEnd) {
                break;
            }
            $nodeResult = $this->buildNextNode($isSubquery);
        }

        return new NodeGroup($nodes, $prohibited);
    }

    private function buildNextNode(bool $isSubquery): NodeResult
    {
        $tokenUnderConstruction = '';
        $inQuotes = false;
        $fieldName = '';
        $prohibited = false;

        while ($this->position < strlen($this->query)) {
            $char = $this->query[$this->position];

            // If we're in a quoted string, we treat all characters except another quote as ordinary characters
            if ($inQuotes) {
                if ($char !== '"') {
                    $tokenUnderConstruction .= $char;
                    $this->position++;
                    continue;
                } else {
                    $this->position++;
                    return new NodeResult(
                        $this->createNode($tokenUnderConstruction, $fieldName, $prohibited),
                        false
                    );
                }
            }

            switch ($char) {
                case '-':
                    if ($tokenUnderConstruction === '') {
                        // A minus sign at the beginning of a token indicates prohibition
                        $prohibited = true;
                    } else {
                        // In any other location, it's just a normal character
                        $tokenUnderConstruction .= $char;
                    }
                    break;

                case '"':
                    if ($tokenUnderConstruction === '') {
                        // A quote sign at the beginning of a token indicates the start of a quoted string
                        $inQuotes = true;
                    } else {
                        // In any other location, it's just a normal character
                        $tokenUnderConstruction .= $char;
                    }
                    break;

                case '(':
                    if ($tokenUnderConstruction === '') {
                        // A left parentheses at the beginning of a token indicates the start of a subquery
                        $this->position++;
                        return new NodeResult($this->buildNodeGroup(true, $prohibited),
                            false
                        );
                    } else {
                        // In any other location, it's just a normal character
                        $tokenUnderConstruction .= $char;
                    }
                    break;

                case ')':
                    // A right parentheses while in a subquery means the subquery ended,
                    // thus also signaling the end of any node currently being built
                    if ($isSubquery) {
                        $this->position++;
                        return new NodeResult(
                            $tokenUnderConstruction !== ''
                            ? $this->createNode($tokenUnderConstruction, $fieldName, $prohibited)
                            : null,
                            true
                        );
                    }
                    // In any other location, it's just a normal character
                    $tokenUnderConstruction .= $char;
                    break;


                case ':':
                    if ($tokenUnderConstruction !== '') {
                        // If we meet a colon with a left-hand side string, we know we're in a field and are about to set up the value
                        $fieldName = $tokenUnderConstruction;
                        $tokenUnderConstruction = '';
                    } else {
                        // In any other location, it's just a normal character
                        $tokenUnderConstruction .= $char;
                    }
                    break;

                case ' ':
                    // A space indicates the end of a token construction if non-empty, otherwise it's just ignored
                    if ($tokenUnderConstruction !== '') {
                        $this->position++;
                        return new NodeResult(
                            $this->createNode($tokenUnderConstruction, $fieldName, $prohibited),
                            false
                        );
                    }
                    break;

                default:
                    $tokenUnderConstruction .= $char;
            }

            $this->position++;
        }

        $finalNode = $tokenUnderConstruction !== '' || $fieldName !== ''
        ? $this->createNode($tokenUnderConstruction, $fieldName, $prohibited)
        : null;

        return new NodeResult($finalNode, true);
    }

    private function createNode(string $token, string $fieldName, bool $prohibited): Node
    {
        if (strlen($fieldName) > 0) {
            return new FieldNode(trim($fieldName), trim($token), $prohibited);
        }
        return new StringNode(trim($token), $prohibited);
    }
}

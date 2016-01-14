<?php

namespace AssocField;

/**
 * Dataset
 * Parses the input data.
 * Generates a list of meanings. Builds a connection.
 */
class Dataset
{
    public $meaningsList = [];
    public $definitionsList = [];

    public function loadPlain($source)
    {
        // Passed $this
        if (is_object($source)) {
            $source = $source->scriptName;
        }

        $filename = AF_PATH . '/dataset/' . $source . '.txt';

        $rows = explode("\n", file_get_contents($filename));

        foreach ($rows as $line => $row) {

            // Comment
            if (substr($row, 0, 1) == '#' || empty(trim($row))) {
                continue;
            }

            // Parse definition
            list($one, $two, $relevance) = explode("|", trim($row));

            if (empty($one) || empty($two) || empty($relevance) || !is_numeric($relevance)) {
                throw new Exception('Invalid definition at ' . $line . ' line.' . "\n" . $row);
            }

            // Store meanings and definition
            $one = Utils::normalizeMeaning($one);
            $this->addMeaning($one);

            $two = Utils::normalizeMeaning($two);
            $this->addMeaning($two);

            $this->addDefinition($one, $two, $relevance);
        }
    }

    public function addMeaning($meaning)
    {
        if (!isset($this->meaningsList[$meaning])) {
            $this->meaningsList[$meaning] = 1;
        } else {
            $this->meaningsList[$meaning]++;
        }
    }

    public function addDefinition($one, $two, $relevance)
    {
        $this->definitionsList[] = [
            $one, $two, $relevance
        ];
    }
}
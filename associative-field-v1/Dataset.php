<?php

/**
 * Dataset
 */
class Dataset
{
    public $meaningsList = [];
    public $definitionsList = [];

    public function loadFromFile($filename)
    {
        $rows = explode("\n", file_get_contents($filename));

        foreach ($rows as $line => $row) {
            list($one, $two, $relevance) = explode("|", trim($row));

            if (empty($one) || empty($two) || empty($relevance) || !is_numeric($relevance)) {
                throw new Exception('Invalid definition at ' . $line . ' line.' . "\n" . $row);
            }

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
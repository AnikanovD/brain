<?php

// Bootstrap
require_once 'Bootstrap.php';

// Loading dataset
$dataset->loadFromFile('dataset-pet.txt');

// Creating entities
foreach ($dataset->meaningsList as $meaning => $number) {
    $field->createEntity($meaning);
}

// Creating links
foreach ($dataset->definitionsList as $definition) {
    $entityOne = $field->findEntityByMeaning($definition[0]);
    $entityTwo = $field->findEntityByMeaning($definition[1]);
    $relevance = $definition[2];

    $entityOne->linkUni($entityTwo, $relevance);
}

// Built associative field
// What's next?
//
// Lets try build a chain of entities

print_r($consciousness->showProjection());
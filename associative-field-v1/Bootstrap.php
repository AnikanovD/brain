<?php

/**
 * Theory of associative fields
 *
 * The main purpose is to formalize the notion of "image" and implement mechanisms of attention.
 * Keep it simple!
 */

require_once 'Utils.php';
require_once 'Dataset.php';

require_once 'Entity.php';
require_once 'Field.php';
require_once 'Image.php';
require_once 'Consciousness.php';


$field = new Field;
$consciousness = new Consciousness($field);

$dataset = new Dataset;

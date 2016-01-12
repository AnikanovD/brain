<?php
/**
 * Theory of associative fields
 *
 * The main purpose is to formalize the notion of "image" and implement mechanisms of attention.
 * Keep it simple!
 */

define('AF_PATH', __DIR__);

require AF_PATH . '/vendor/autoload.php';

(new AssocField\AssocField)->runScript();
<?php

/**
 * Utils
 */
class Utils
{
    /**
     * Normalize string representation of meaning
     */
    public static function normalizeMeaning($meaning)
    {
        return mb_strtolower(trim($meaning));
    }

    /**
     * Log
     */
    public static function log($var)
    {
        if (is_string($var)) {
            echo $var;
        } elseif (is_array($var)) {
            echo str_replace("\n", '', print_r($var, true));
        }

        echo PHP_EOL;
    }

    /**
     * Dump links into graphical
     */
    public static function dumpLinks($var)
    {
        echo PHP_EOL;
    }

}
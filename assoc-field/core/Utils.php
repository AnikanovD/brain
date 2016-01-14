<?php

namespace AssocField;

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
     * Dump
     */
    public static function dumpChains()
    {
        $chains = func_get_args();

        foreach ($chains as $index => $chain) {
            $chains[$index] = self::translateChain($chains[$index]);
        }

        $data = json_encode($chains);
        $dumpName = AssocField::$i->scriptName . '_chains.json';

        file_put_contents($dumpName, $data);
        AssocField::log('Dumped');
    }

    public static function translateChain($arr)
    {
        $_arr = [];

        foreach ($arr as $k => $v) {
            $entity = AssocField::$field->findEntityById($k);
            if (is_array($v)) {
                $_arr[$entity->meaning] = self::translateChain($v);
            } else {
                $_arr[$entity->meaning] = $v;
            }
        }

        return $_arr;
    }

}
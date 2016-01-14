<?php

namespace AssocField;

/**
 * AssocField
 */
class AssocField
{
    public $scriptName;
    public $scriptPath;

    public static $i;
    public static $field;
    public static $dataset;

    /**
     * Perform prepares
     */
    public function __construct()
    {
        if (isset(self::$i)) {
            throw new \Exception('AssocField already instanced');
        }

        mb_internal_encoding('UTF-8');
        $argv = $_SERVER['argv'];

        // Welcome!
        self::showWelcome();

        // Shows help and bye-bye
        if (!isset($argv[1]) || in_array($argv[1], ['-h', '--help', 'help'])) {
            self::showHelp();
            exit;
        }

        // Checks name
        $this->scriptName = trim($argv[1]);

        if (!preg_match('/^[a-z0-9\-]+$/', $this->scriptName)) {
            self::log('Invalid script name');
        }

        // Checks file
        $this->scriptPath = AF_PATH . '/scripts/' . $this->scriptName . '.php';

        if (!file_exists($this->scriptPath)) {
            self::log('Script not found (' . $this->scriptPath . ')');
        }

        // Last prepares
        $this->initDomains();
        self::$i = $this;
    }

    /**
     * Defines static property
     */
    public function initDomains()
    {
        self::$field = new Field;
        self::$dataset = new Dataset;
    }

    /**
     * Run script
     */
    public function runScript()
    {
        // Preparing the environment
        $field = self::$field;
        $dataset = self::$dataset;

        // Run
        self::bar();
        self::log();

        include $this->scriptPath;

        self::log();
        self::bar();
        self::nl();
    }

    /**
     * Print welcome message
     */
    public static function showWelcome()
    {
        self::nl();
        self::log('| | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | |');
        self::log('| | | AssocField                                                                          |');
        self::log('| | | Implementation of the Theory of Associative Fields                                  |');
        self::log('| | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | | |');
        self::nl();
    }

    /**
     * Print help message
     */
    public static function showHelp()
    {
        self::log('Usage: ./launcher <script>');
    }

    /**
     * Print string
     */
    public static function log($str = '')
    {
        echo '  ' . $str . "\n";
    }

    /**
     * Print a new line
     */
    public static function nl($str = '')
    {
        echo "\n";
    }

    /**
     * Print a dashed line
     */
    public static function bar($duration = 0)
    {
        $divider = 46;
        echo '  ';

        if ($duration > 0) {
            for ($i = 0; $i < $divider; $i++) {
                echo '- ';
                usleep(($duration * 1000) / $divider);
            }
        } else {
            echo str_repeat('- ', 46);
        }

        self::nl();
    }

}

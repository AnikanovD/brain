<?php

namespace AssocField;

/**
 * AssocField
 */
class AssocField
{
    public $scriptName;
    public $scriptPath;

    /**
     * Perform prepares
     */
    public function __construct()
    {
        // Arguments
        global $argv, $argc;

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
    }

    /**
     * Run script
     */
    public function runScript()
    {
        // Preparing the environment
        $field = new Field;
        $dataset = new Dataset;
        $consciousness = new Consciousness($field);

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

        self::bar(2000);
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
     * Print new line
     */
    public static function nl($str = '')
    {
        echo "\n";
    }

    /**
     * Print string
     */
    public static function bar($duration = 200)
    {
        $divider = 46;
        echo '  ';

        for ($i = 0; $i < $divider; $i++) {
            echo '- ';
            usleep(($duration * 1000) / $divider);
        }

        self::nl();
    }

}

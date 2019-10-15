<?php

class Env
{
	public static $showTrace = false;

	public static function stage($message)
	{
		echo PHP_EOL . ' # ' . $message . PHP_EOL;
	}

	public static function info($message)
	{
		echo '    ' . $message . PHP_EOL;
	}

	public static function warning($message)
	{
		echo PHP_EOL . ' ?!>  ' .  $message . PHP_EOL . PHP_EOL;
	}

	public static function trace($message)
	{
		if (self::$showTrace) {
			echo '(i)>  ' . $message . PHP_EOL;
		}
	}
}
<?php

class ApplicationMode
{
	const
	MODULE_WEB = 'web';

	public static $maintenance = [
		self::MODULE_WEB => FALSE,
	];

	private static $ips = [
		'94.143.169.97',
		'127.0.0.1',
	];

	private static $mode_domains = [
		'invoices.iwory.localhost' => 'development_localhost',
		'invoices.iwory.cz' => 'development',
	];

	private static $mode_development = [
		'development_localhost',
		'development',
	];

	private static $mode_production = [
		'production',
	];

    public static function debug($forced = FALSE) {
		return $forced ? self::$ips : (in_array(self::mode(), self::$mode_development)) ? self::$ips : FALSE;
    }

	public static function server__name() {
		return str_replace('www.', '', $_SERVER['SERVER_NAME']);
	}

	public static function ips__allowed() {
		return self::$ips;
	}

	public static function ip__client() {
		return $_SERVER['REMOTE_ADDR'];
	}

	public static function mode() {
		if(array_key_exists(self::server__name(), self::$mode_domains)) {
			return self::$mode_domains[self::server__name()];
		}
		throw new Exception('Application mode is not defined!');
	}

	public static function maintenance($module) {
		return self::$maintenance[$module] === TRUE ? (in_array(self::ip__client(), self::$ips)) ? FALSE : TRUE : FALSE;
	}

	public static function is_production() {
		return in_array(self::mode(), self::$mode_production);
	}

	public static function is_development() {
		return in_array(self::mode(), self::$mode_development);
	}
}
<?php
namespace App\Helper;


class String
{
	public static function fill($string, $default = NULL) {
		return empty($string) ? $default : $string;
	}
}
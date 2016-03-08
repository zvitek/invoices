<?php
namespace App\Helper;

use Nette\Utils;

class Date
{
	public static function dFormat($dateTime, $format = 'd. m. Y') {
		if($dateTime instanceof Utils\DateTime || $dateTime instanceof \Dibi\DateTime || $dateTime instanceof \DateTime) {
			$dt = new \DateTime($dateTime);
			return $dt->format($format);
		}
		return NULL;
	}
}
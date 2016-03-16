<?php
namespace App\Helper;

use Nette\Utils;

class DateTime
{
	public static function date_Format($dateTime, $format = 'd. m. Y') {
		if($dateTime instanceof Utils\DateTime || $dateTime instanceof \Dibi\DateTime || $dateTime instanceof \DateTime) {
			$dt = new \DateTime($dateTime);
			return $dt->format($format);
		}
		return NULL;
	}

	public static function date_Database($date) {
		$date = explode('.', str_replace(' ', '', $date));
		$date = sprintf('%s-%s-%s', $date[0], $date[1], $date[2]);
		return new Utils\DateTime($date);
	}
}
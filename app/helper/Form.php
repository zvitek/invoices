<?php
namespace App\Helper;


class Form
{
	public static function clear__Phone($phone) {
		return str_replace('+420', '', $phone);
	}
}
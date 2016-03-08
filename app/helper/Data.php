<?php
namespace App\Helper;


class Data
{
	public static function pick($value, $default = NULL) {
		return empty($value) ? $default : $value;
	}

	public static function prepare_ids($data, $column = 'id') {
		$buffer = [];
		if(count($data)) {
			foreach($data as $item) {
				$buffer[] = $item[$column];
			}
		}
		return $buffer;
	}

	public static function return__empty($data) {
		return is_null($data) || is_numeric($data) ? NULL : [];
	}

	public static function pager__info($offset, $limit, $count, $count_all) {
		return [
			'from' => $offset,
			'to' => $offset + ($count < $limit ? $count : $limit),
			'count' => $count_all,
		];
	}
}
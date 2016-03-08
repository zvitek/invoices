<?php
namespace App\Helper;


class Database
{
	public static function data_structure($data) {
		if(is_array($data)) {
			$data = array_filter($data);
			if(!count($data)) {
				return NULL;
			}
			return $data;
		}
		elseif(is_numeric($data)) {
			return $data;
		}
		return NULL;
	}

	public static function selection($data, $column = 'id') {
		if(is_array($data)) {
			$data = array_filter($data);
			if(!count($data)) {
				return NULL;
			}
			return [$column . '%in' => $data];
		}
		elseif(is_numeric($data)) {
			return [$column . '%i' => $data];
		}
		return NULL;
	}
}
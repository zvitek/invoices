<?php
namespace App\Config;


class Routes
{
	const
	USER_LOGOUT = '__logout!',
	USER_LOGIN = ':Web:User:Page:login';

	const
	INVOICE_LIST = ':Web:Invoice:Page:default',
	INVOICE_EDIT = ':Web:Invoice:Page:edit';

	const
	HOME_PAGE = ':Web:Page:default';

	public static function get($route, $full = FALSE) {
		return $full ? '//' . $route : $route;
	}
}
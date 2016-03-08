<?php
namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\SimpleRouter;
use Nette\Utils\Strings;

class InvoiceRouterFactory
{
	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter() {
		$router = new RouteList('Web:Invoice');
		$router[] = new Route('invoice/<action>', 'Page:default');
		return $router;
	}
}

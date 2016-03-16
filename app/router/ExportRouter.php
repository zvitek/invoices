<?php
namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\SimpleRouter;
use Nette\Utils\Strings;

class ExportRouterFactory
{
	/**
	 * @return \Nette\Application\IRouter
	 */
	public static function createRouter() {
		$router = new RouteList('Web:Export');
		$router[] = new Route('export/<presenter>/<action>', 'Page:default');
		return $router;
	}
}

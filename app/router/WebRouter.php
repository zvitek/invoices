<?php
namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class WebRouterFactory
{
	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter() {
		$router = new RouteList();
		$router[] = UserRouterFactory::createRouter();
		$router[] = InvoiceRouterFactory::createRouter();
		$router[] = $router_Web = new RouteList('Web');
		$router_Web[] = new Route('<action>[/<id>]', 'Page:default');

		return $router;
	}
}

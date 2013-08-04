<?php

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;



class RouterFactory
{

	/**
	 * @var Nette\Http\Request
	 */
	private $httpRequest;



	public function __construct(Nette\Http\Request $httpRequest)
	{
		$this->httpRequest = $httpRequest;
	}



	/**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$flags = $this->httpRequest->isSecured() ? Route::SECURED : 0;

		$router = new RouteList();
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default', $flags);

		return $router;
	}

}

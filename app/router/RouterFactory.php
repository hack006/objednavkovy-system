<?php

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
        $router[] = new Route('admin/<presenter>/<action>[/<id>]', array(
            'module' => 'Admin',
            'presenter' => 'Default',
            'action' => 'default',
            'id' => NULL
        ));
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}

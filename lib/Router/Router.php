<?php
namespace Router;
class Router
{
	const METHOD_GET = 'GET',
		METHOD_POST = 'POST',
		METHOD_PUT = 'PUT',
		METHOD_PATCH = 'PATCH',
		METHOD_DELETE = 'DELETE';
	static private $http_methods = array(self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH, self::METHOD_DELETE);
	private $routes, $root;

	public function __construct($dsl)
	{
		$routable = new Routable('');
		$dsl($routable);
		$this->routes = $routable->getRoutes();
		$this->root = $routable->getRoot();
	}
	
	public function route($to)
	{
		
	}
	
	public function getRoutes()
	{
		$routes = $this->routes;
		if ($this->root)
			$routes[''] = $routes['/'] = $this->root;
		return $routes;
	}
	
	static public function draw($dsl)
	{
		$instance = new self($dsl);
		return $instance;
	}
	static public function getHttpMethods()
	{
		return self::$http_methods;
	}
}
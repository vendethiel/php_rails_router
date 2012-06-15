<?php
namespace Router;
class Router
{
	const METHOD_GET = 'GET',
		METHOD_POST = 'POST',
		METHOD_PUT = 'PUT',
		METHOD_PATCH = 'PATCH',
		METHOD_DELETE = 'DELETE',
		
		FORMAT_HTML = 'html',
		DEFAULT_FORMAT = 'html';
	static private $http_methods = array(self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_PATCH, self::METHOD_DELETE);
	private $routes, $root;

	/**
	 * constructs a new Router
	 *
	 * @variable Closure $dsl The closure to eval
	 */
	public function __construct($dsl)
	{
		$routable = new Routable('');
		$dsl($routable);
		$this->routes = $routable->getRoutes();
		$this->root = $routable->getRoot();
	}
	
	/**
	 * Dispatches an URL
	 *
	 * @param string $url URL to parse
	 * @param string $format Format to return (html, json, xml, ...)
	 * @param string $via Method accessing, GET by default
	 * @return Dispatcher the dispatcher instance
	 */
	public function dispatch($url, $format = self::DEFAULT_FORMAT, $via = self::METHOD_GET)
	{
		return $this->getDispatcher($url, $via)->dispatch($format);
	}
	
	/**
	 * Dispatches a named route
	 * No way to pass parameters ATM
	 *
	 * @param string $name Route name
	 * @param string $format Format to return - may be defaulted to actual request's format, if called in an helper
	 * @return Dispatcher the dispatcher instance
	 */
	public function dispatchName($name, $format = self::DEFAULT_FORMAT)
	{
		return $this->getDispatcher($url, true, 'as')->dispatch($format);
	}
	
	/**
	 * Creates a dispatcher
	 * Useful for testing
	 *
	 * @param mixed $compare String to compare
	 * @param string $via Method accessing - not defaulted
	 * @param string $by What is the comparison made with ?
	 * @return Dispatcher the dispatcher instance
	 */
	public function getDispatcher($compare, $via, $by = 'find')
	{
		return Dispatcher::create($compare, $via, $by, $this->routes, $this->root);
	}
	
	/**
	 * Returns routes list
	 * Useful for testing
	 * Note: also adds root as '' and '/'
	 */
	public function getRoutes()
	{
		$routes = $this->routes;
		if ($this->root)
			$routes[''] = $routes['/'] = $this->root;
		return $routes;
	}
	
	/**
	 * DSL-izer !
	 * @see __construct
	 *
	 * @return Router
	 */
	static public function draw($dsl)
	{
		$instance = new self($dsl);
		return $instance;
	}
	
	/**
	 * @return array available HTTP Methods
	 */
	static public function getHttpMethods()
	{
		return self::$http_methods;
	}
}
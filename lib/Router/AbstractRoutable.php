<?php
namespace Router;

abstract class AbstractRoutable
{
	protected $shallow_path, $shallow_name, $ns, $constraints = array(), $routes = array();

	/**
	 * @return array Routes list
	 */
	public function getRoutes()
	{
		return $this->routes;
	}
	
	/**
	 * Creates a `Resources` and allows to pass a DSL in
	 *
	 * @param string $name `Resources`'s name
	 * @param closure $closure optional DSL to eval
	 * @param array $options `Resources` options
	 */
	public function resources($name, $closure = null, $options = array())
	{
		$resources = new RoutableResources($name, $closure, $options);
		$this->addSubRoutes($resources->getRoutes());
	}
	
	/**
	 * Creates a `Resource` and allows to pass a DSL in
	 *
	 * @param string $name `Resource`'s name
	 * @param closure $closure optional DSL to eval
	 * @param array $options `Resource` options
	 */
	public function resource($name, $closure = null, $options = array())
	{
		$resource = new RoutableResource($name, $closure, $options);
		$this->addSubRoutes($resource->getRoutes());
	}
	
	/**
	 * Adds the routes as "sub-routes" :
	 * - adds the shallow path
	 * - adds the shallow route name
	 * - adds the controller namespace
	 * - adds the constraints
	 * - formats the :*_id constraint 
	 */
	protected function addSubRoutes($routes)
	{
		if (!is_array($routes))
			$routes = $routes->getRoutes();
	
		$trimmed_shallow_path = ltrim($this->shallow_path, '/');
		if ($trimmed_shallow_path == '/')
			$trimmed_shallow_path = '';
		foreach ($routes as $find => $route)
		{
			//add the shallow path
			$route['find'] = $trimmed_shallow_path . trim($route['find'], '/');
			
			//rewrite the find if parameters
			if (strpos($route['find'], ':') !== false)
			{
				$f = explode('/', $route['find']);
				$last_index = count($f) - 1;
				if ($f[$last_index - 1][0] == ':' && substr($f[$last_index-1], -3) == '_id')
				{
					$parameter_name = substr($f[$last_index - 1], 1);
					$f[$last_index - 1] = ':id';
					
					if (isset($route['constraints']) && isset($route['constraints'][$parameter_name]))
					{
						$constraint = $route['constraints'][$parameter_name];
						unset($route['constraints'][$parameter_name]);
						$route['constraints']['id'] = $constraint;
					}
				}
				
				$route['find'] = implode('/', $f);
			}
			
			//add the namespace
			if ($this->ns)
				$route['to'] = ucfirst($this->ns) . ucfirst($route['to']);
			
			//add the shallow route name
			if ($route['as'])
				$route['as'] = trim($this->shallow_name . $route['as'], '_');
			
			//add the constraints
			if (isset($route['constraints']))
				$route['constraints'] = array_merge($this->constraints, $route['constraints']);
			else
				$route['constraints'] = $this->constraints;
			
			//merge it !
			$this->routes[] = $route;
		}
	}
}
<?php
namespace Router;

abstract class AbstractRoutable
{
	protected $shallow_path, $shallow_name, $ns, $routes = array();

	public function getRoutes()
	{
		return $this->routes;
	}
	
	public function resources($name, $closure = null, $options = array())
	{
		$resources = new RoutableResources($name, $closure, $options);
		$this->addSubRoutes($resources->getRoutes());
	}
	
	public function resource($name, $closure = null, $options = array())
	{
		$resource = new RoutableResource($name, $closure, $options);
		$this->addSubRoutes($resource->getRoutes());
	}
	
	public function ns($ns, $closure = null)
	{
		$ns = new Routable('', $ns, $ns);
		$closure($ns);
		$this->addSubRoutes($ns->getRoutes());
	}
	
	protected function addSubRoutes($routes)
	{
		$trimmed_shallow_path = ltrim($this->shallow_path, '/');
		if ($trimmed_shallow_path == '/')
			$trimmed_shallow_path = '';
		foreach ($routes as $find => $route)
		{
			$route['find'] = $trimmed_shallow_path . trim($route['find'], '/');
			
			if (strpos($route['find'], ':') !== false)
			{
				$f = explode('/', $route['find']);
				$last_index = count($f) - 1;
				if ($f[$last_index-1][0] == ':' && substr($f[$last_index-1], -3) == '_id')
					$f[$last_index-1] = ':id';
				$route['find'] = implode('/', $f);
			}
			if ($this->ns)
				$route['to'] = $this->ns . $route['to'];
			if ($route['as'])
				$route['as'] = trim($this->shallow_name . $route['as'], '_');
			
			$this->routes[] = $route;
		}
	}
}
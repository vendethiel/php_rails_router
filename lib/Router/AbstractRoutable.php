<?php
namespace Router;

abstract class AbstractRoutable
{
	protected $shallow_path, $shallow_name, $ns, $constraints = array(), $routes = array(), $sub_routes = array();

	/**
	 * @return array Routes list
	 */
	public function getRoutes()
	{
		$sub_routes = array();
		$trimmed_shallow_path = ltrim($this->shallow_path, '/');
		if ($trimmed_shallow_path == '/')
			$trimmed_shallow_path = '';
		foreach ($this->sub_routes as $sub_route)
		{
			//add the shallow path
			$sub_route['find'] = $trimmed_shallow_path . trim($sub_route['find'], '/');
			
			//rewrite the find if parameters
			if (strpos($sub_route['find'], ':') !== false)
			{
				$f = explode('/', $sub_route['find']);
				$last_index = count($f) - 1;
				if ($f[$last_index - 1][0] == ':' && substr($f[$last_index-1], -3) == '_id')
				{
					$parameter_name = substr($f[$last_index - 1], 1);
					$f[$last_index - 1] = ':id';
					
					if (isset($sub_route['constraints']) && isset($sub_route['constraints'][$parameter_name]))
					{
						$constraint = $sub_route['constraints'][$parameter_name];
						unset($sub_route['constraints'][$parameter_name]);
						$sub_route['constraints']['id'] = $constraint;
					}
				}
				
				$sub_route['find'] = implode('/', $f);
			}
		
			
			//add the shallow route name
			if ($sub_route['as'])
			{
				if ('_' === $sub_route['as'])
					$sub_route['as'] = basename($sub_route['find']);
				else
					$sub_route['as'] = trim($this->shallow_name . $sub_route['as'], '_');
			}
			
			$sub_route['constraints'] = array_merge($this->constraints, $sub_route['constraints']);
			
			if (strpos($sub_route['as'], '!') !== false)
			{ //we need to rewrite plural to singular
				$as_parts = explode('_', $sub_route['as']);

				foreach ($as_parts as $i => &$as_part)
				{
					if (!strlen($as_part))
						continue;
					
					if ($as_part[0] == '!')
					{
						$as_parts[$i - 1] = \Inflector::singularize($as_parts[$i - 1]);
						$as_part = substr($as_part, 1);
					}
				}
				
				$sub_route['as'] = implode('_', $as_parts);
			}
			
			/** Disallow followed same name (article_article)
			 * until I find why I have
			 *
	array
      'find' => string 'session/admin/article/:article_id/be/ah' (length=39)
      'to' => string 'Ah#b' (length=4)
      'as' => string 'session_admin_article_article_be_ah' (length=27)
      'via' => 
        array
          0 => string 'GET' (length=3)
      'constraints' => 
        array
          empty
			 */
			$as_parts = explode('_', $sub_route['as']);
			$corrected_as_parts = array();
			$prev_as_part = null;
			foreach ($as_parts as $as_part)
			{
				if ($prev_as_part != $as_part)
					$corrected_as_parts[] = $as_part;
				$prev_as_part = $as_part;
			}
			$sub_route['as'] = implode('_', $corrected_as_parts);
		
			$sub_routes[] = $sub_route;
		}
			
		return array_merge($sub_routes, $this->routes);
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

		$this->sub_routes = array_merge($this->sub_routes, $routes);
		/*
	
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
			{
				//@todo why 
				/*
				array
  'find' => string 'article/:article_id/manage/reviews/new' (length=38)
  'to' => string 'Reviews#new' (length=11)
  'as' => string 'article_new' (length=11)
  'via' => 
    array
      0 => string 'GET' (length=3)
  'constraints' => 
    array
      empty
				if ('_' === $route['as'])
					$route['as'] = end(explode('/', $route['find']));
				else
					$route['as'] = trim($this->shallow_name . $route['as'], '_');
			}
			
			//add the constraints
			if (isset($route['constraints']))
				$route['constraints'] = array_merge($this->constraints, $route['constraints']);
			else
				$route['constraints'] = $this->constraints;
			
			//merge it !
			$this->routes[] = $route;
		}
			*/
	}
}
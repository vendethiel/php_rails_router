<?php
namespace Router;

class Dispatcher
{
	private $route, $variables = array();

	/**
	 * Constructs a dispatcher
	 *
	 * @param array $route Route
	 * @param array $variables Route variables
	 */
	public function __construct($route, $variables = array())
	{
		$this->route = $route;
		$this->variables = $variables;
	}
	
	/**
	 * Dispatches to a format
	 *
	 * @param string $format Format to return (html, json, xml, ...)
	 */
	public function dispatch($format)
	{
		list($controller, $action) = explode('#', $this->route['to']);
		$class = 'Controller\\' . $controller;
		
		$controller = new $class;
		$controller->execute($action, $this->variables, $format);
		unset($controller);
	}
	
	/**
	 * Finds a working route and creates a dispatcher for it
	 *
	 * @param mixed $compare String to compare
	 * @param string $via Method accessing - not defaulted
	 * @param string $by What is the comparison made with ?
	 * @param array $routes Routes list
	 * @param array $root Root route - optional
	 * @return Dispatcher
	 */
	static public function create($compare, $via, $by, $routes, $root = null)
	{
		//N.B -> if we're not using $by='find', we need to pass variables
		// but adding another arguments seems too much
		foreach ($routes as $route)
		{
			if ($via !== true && !in_array($via, $route['via']))
				continue;
			
			//list of variables to be passed
			$variables = array();
				
			if ('find' === $by)
			{ //if we're search by the 'find' attribute (path), we need some customs things ...
				$find_parts = explode('/', $route['find']); //['products', ':id']
				$compare_parts = explode('/', $compare); //['products', '5']
				
				if (count($find_parts) != count($compare_parts))
					continue; //we don't have the same number of part
				
				foreach ($find_parts as $i => $find_part)
				{ //we compare each part
					if ($find_part[0] == ':') //it's :id, so it's a variable
						$variables[substr($find_part, 1)] = $compare_parts[$i];
					else if ($find_part != $compare_parts[$i]) //it's <'products' == 'products'>
						continue 2; //2 to skip to the next route
				}
				
				foreach ($variables as $name => $value)
				{ //now, for each variables, let's check constraints
					if (isset($route['constraints'][$name]))
					{ //we don't need to foreach constraints + throw error when no variable, since variables are already checked above
						if (!preg_match('`' . $route['constraints'][$name] . '`', $value))
							continue 2; //the value doesn't match the constraint associated, skip to the next route
					}
				}
			}
			else if ($route[$by] !== $compare) //if the attribute doesn't match exactly the one we were looking for
				continue; //skip to the next route
			
			return new self($route, $variables);
		}
		
		if ($root) //we have a root, return it...
			return new self($root);
		throw new \RuntimeException('Error 404'); //@todo add an ErrorController ?
	}
}
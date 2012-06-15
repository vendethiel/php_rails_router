<?php
namespace Router;

class RoutableResource extends RoutableResources
{
	protected $name;

	/**
	 * Constructs a `Resources` and allows to pass a DSL in
	 *
	 * @param string $name `Resources`'s name
	 * @param closure $closure optional DSL to eval
	 * @param array $options `Resources` options
	 */
	public function __construct($name, $closure, $options)
	{
		$this->name = $name;
		$singularized_name = \Inflector::singularize($this->name);
		$this->shallow_name = $name . '_';
	
		//no closure passed, only the options
		if (is_array($closure) && array() === $options)
		{ //resources('a', array('only' => ...))
			$options = $closure;
			$closure = null;
		}
		
		//CRUD actions
		$base_methods = array('show', 'new', 'create', 'edit', 'update', 'destroy');
		$methods = $base_methods;
		if (isset($options['only'])) //resources('articles', array('only' => 'index'))
			$methods = array_intersect($methods, $options['only']); //should check ?
		if (isset($options['except']))
			$methods = array_diff($methods, $options['except']);

		//fill in the array with true/false values
		$has = array();
		foreach ($base_methods as $base_method)
			$has[$base_method] = false;
		foreach ($methods as $method)
			$has[$method] = true;

		$this->createRoutes($has);
		
		if ($closure)
			$this->collection($closure);
	}
	
	protected function createRoutes($has)
	{
		$name = $this->name;
		$this->collection(function ($r) use ($has, $name)
		{
			if ($has['show'])
				$r->get($name . '#show');
			if ($has['create'])
				$r->post($name . '#create');
			if ($has['update'])
				$r->put($name . '#update');
			if ($has['destroy'])
				$r->delete($name . '#destroy');
			if ($has['new'])
				$r->get('new', $name . '#new');
			if ($has['edit'])
				$r->get('edit', $name . '#edit');
		});
	}
}
<?php
namespace Router;

class RoutableResource extends RoutableResources
{
	protected $name;
	public function __construct($name, $closure, $options)
	{
		$this->name = $name;
		$singularized_name = \Inflector::singularize($this->name);
	
		if (is_array($closure) && array() === $options)
		{ //resources('a', array('only' => ...))
			$options = $closure;
			$closure = null;
		}
		
		$base_methods = array('show', 'new', 'create', 'edit', 'update', 'destroy');
		$methods = $base_methods;
		if (isset($options['only'])) //resources('articles', array('only' => 'index'))
			$methods = (array) $options['only'];
		if (isset($options['except']))
		{
			$count_methods = count($methods);
			$methods = array_intersect($methods, (array) $options['except']);
			if (count($methods) > $count_methods)
				throw new \InvalidArgumentException('You can\'t add method(s) through :except');
		}

		$has = array();
		foreach ($base_methods as $base_method)
			$has[$base_method] = false;
		foreach ($methods as $method)
			$has[$method] = true;

		$this->shallow_name = $singularized_name . '_';
		$prev_shallow_path = $this->shallow_path;
		$this->shallow_path = $singularized_name . '/';

		$this->createRoutes($has);
				
		if ($closure)
			$this->collection($closure);

		$this->shallow_path = $prev_shallow_path;
		$this->shallow_name ='';
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
<?php
namespace Router;

class RoutableResources extends AbstractRoutable
{
	protected $name;
	public function __construct($name, $closure, $options)
	{
		$this->name = $name;
		$singularized_name = \Inflector::singularize($this->name);
//		$this->shallow_name = $singularized_name . '_';
	
		if (is_array($closure) && array() === $options)
		{ //resources('a', array('only' => ...))
			$options = $closure;
			$closure = null;
		}
	
		$base_methods = array('index', 'show', 'new', 'create', 'edit', 'update', 'destroy');
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
		$this->shallow_path = $this->name . '/:' . $singularized_name . '_id/';
		if ($closure)
			$closure($this);
		$this->shallow_path = $prev_shallow_path;
		$this->shallow_name = '';
		
		$this->createRoutes($has);
	}
	
	protected function createRoutes($has)
	{
		$name = $this->name;
		$singularized_name = \Inflector::singularize($name);

		//examples given with $name = 'articles'
		//get /articles => Articles#index
		$this->collection(function ($r) use ($has, $name)
		{
			if ($has['index'])
				$r->get($name . '#index');
			if ($has['new'])
				$r->get('new', $name . '#new');
			if ($has['create'])
				$r->post($name . '#update');
		});
		
		$this->member(function ($r) use ($has, $name, $singularized_name)
		{
			if ($has['show'])
				$r->get($name . '#show', '', $singularized_name);
			if ($has['edit'])
				$r->get('edit', $name . '#edit');
			if ($has['update'])
				$r->put($name . '#update');
			if ($has['destroy'])
				$r->delete($name . '#destroy');
		});
	}
	
	public function collection($closure = null, $options = array())
	{
		if (is_array($closure) || is_string($closure))
			$closure = $this->convertArrayToClosure((array) $closure, $options);

		$prev_shallow_name = $this->shallow_name;
		$prev_shallow_path = $this->shallow_path;
		$this->shallow_name = $this->name . '_';
		$this->shallow_path = $this->name . '/';
		$collection = new RoutableCollection($this->name, $closure, $options);
		$this->addSubRoutes($collection->getRoutes());
		$this->shallow_name = $prev_shallow_name;
		$this->shallow_path = $prev_shallow_path;
	}
	public function member($closure = null, $options = array())
	{
		if (is_array($closure) || is_string($closure))
			$closure = $this->convertArrayToClosure((array) $closure, $options);
		
		$this->shallow_path = $this->name . '/:' . \Inflector::singularize($this->name) . '_id/';
		$collection = new RoutableMember($this->name, $closure, $options);
		$this->addSubRoutes($collection->getRoutes());
	}
	
	private function convertArrayToClosure($elements, $options)
	{
		$via = isset($options['via']) ? $options['via'] : Router::METHOD_GET;
		return function ($r) use ($elements, $via)
		{
			foreach ($elements as $element)
				$r->match($element, '', null, $via);
		};
	}
	public function getRoot()
	{
		throw new \RuntimeException('Not implemented');
	}
}
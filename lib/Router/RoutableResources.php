<?php
namespace Router;

class RoutableResources extends AbstractRoutable
{
	protected $name;
	
	/**
	 * Creates a `Resource` and allows to pass a DSL in
	 *
	 * @param string $name `Resource`'s name
	 * @param closure $closure optional DSL to eval
	 * @param array $options `Resource` options
	 */
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
	
		//CRUD actions
		$base_methods = array('index', 'show', 'new', 'create', 'edit', 'update', 'destroy');
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

		//constraint only for the closure
		$this->constraints[$singularized_name . '_id'] = '^[0-9]+$';

		if ($closure)
			$closure($this);

		unset($this->constraints[$singularized_name . '_id']);
		$this->shallow_path = $this->shallow_name = '';
		
		$this->createRoutes($has);
	}
	
	/**
	 * CRUD !
	 *
	 * @param array $has Which actions ?
	 */
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

		$collection = new RoutableCollection($this->name, $closure, $options);
		$this->addSubRoutes($collection->getRoutes());
	}
	public function member($closure = null, $options = array())
	{
		if (is_array($closure) || is_string($closure))
			$closure = $this->convertArrayToClosure((array) $closure, $options);

		$member = new RoutableMember($this->name, $closure, $options);
		$this->addSubRoutes($member->getRoutes());
	}
	
	private function convertArrayToClosure($elements, $options)
	{
		$via = isset($options['via']) ? $options['via'] : Router::METHOD_GET;
		return function ($r) use ($elements, $via)
		{
			foreach ($elements as $element)
				$r->match($element, '', null, array('via' => $via));
		};
	}
	public function getRoot()
	{
		throw new \RuntimeException('Not implemented');
	}
}
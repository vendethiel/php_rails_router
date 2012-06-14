<?php
namespace Router;

class RoutableMember extends NamedRoutable
{
	/**
	 * Constructs a `Member` routable
	 *
	 * @param string $name Resource name
	 * @param closure $closure Closure to eval
	 * @param 
	 */
	public function __construct($name, $closure)
	{
		parent::__construct(\Inflector::singularize($name));
		$this->shallow_path = '/' . $this->name . '/:' . $this->name . '_id/';
		$this->shallow_name = '';
		
		$closure($this);
	}
	
	/**
	 * {@inheritdoc}
	 * Adds a constraint to the match() call
	 */
	public function match($find, $to = '', $as = null, $options = array())
	{
		$singularized_name = \Inflector::singularize($this->name);
	
		if (is_array($as))
		{
			$options = $as;
			$as = null;
		}
		if (!isset($options['constraints']))
			$options['constraints'] = array();
		if (!isset($options['constraints'][$singularized_name . '_id']))
			$options['constraints'][$singularized_name . '_id'] = '^[0-9]+$';
		
		parent::match($find, $to, $as, $options);
	}
	
	/**
	 * Adds a namespace, meaning it adds the Ns to the path, the route name and the controller name
	 *
	 * @param string $ns Namespace name
	 * @param closure $closure DSL to eval
	 */
	public function ns($ns, $closure)
	{
		$ns = new NamespacedRoutable($ns, $this->name . '_' . $ns, $ns);
		$closure($ns);
		$this->addSubRoutes($ns);
	}
	
	
	/**
	 * Adds a scope, meaning it adds the Scope name to the path and the route name
	 *
	 * @param string $scope Scope name
	 * @param closure $closure DSL to eval
	 */
	public function scope($scope, $closure)
	{
		$scope = new ScopedRoutable($this->name, $this->name . '_' . $scope, $scope);
		$closure($scope);
		$this->addSubRoutes($scope);
	}
	/**
	 * Adds a prefix, meaning it adds the Prefix to the path
	 *
	 * @param string $prefix Prefix
	 * @param closure $closure DSL to eval
	 */
	public function prefix($prefix, $closure)
	{
		$prefix = new PrefixedRoutable($prefix, $this->name, '');
		$closure($prefix);
		$this->addSubRoutes($prefix);
	}
	
	
	/**
	 * {@inheritdoc}
	 */
	static protected function guessControllerAsPattern($to)
	{
		list($controller, $action) = explode('#', $to);
		return \Inflector::singularize($controller) . '_' . $action;
	}
}
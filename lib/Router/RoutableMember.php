<?php
namespace Router;

class RoutableMember extends NamedRoutable
{
	/**
	 * Constructs a `Member` routable
	 *
	 * {@inheritdoc}
	 */
	public function __construct($name, $closure)
	{
		parent::__construct(\Inflector::singularize($name));
		$this->shallow_path = '/' . $this->name . '/:' . $this->name . '_id/';
		$this->shallow_name = $this->name . '_';
		
		$closure($this);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function ns($ns_name, $closure)
	{
		$ns = new NamespacedRoutable($ns_name, $ns_name, $ns_name);
		$closure($ns);
		$routable = new Routable('', $ns_name, '');
		$routable->addSubRoutes($ns);
		$this->addSubRoutes($routable);
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
	 * {@inheritdoc}
	 */
	static protected function guessControllerAsPattern($to, $find = null)
	{
		list($controller, $action) = explode('#', $to);
		return \Inflector::singularize($controller) . '_' . $action;
	}
}
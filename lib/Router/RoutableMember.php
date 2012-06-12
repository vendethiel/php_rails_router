<?php
namespace Router;

class RoutableMember extends NamedRoutable
{
	public function __construct($name, $closure, $options)
	{
		parent::__construct($name);
		$this->shallow_path = '';
		$this->shallow_name = '';
		
		$closure($this);
	}
	
	static protected function guessControllerAsPattern($to)
	{
		list($controller, $action) = explode('#', $to);
		return \Inflector::singularize($controller) . '_' . $action;
	}
}
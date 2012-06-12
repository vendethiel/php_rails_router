<?php
namespace Router;

class RoutableCollection extends NamedRoutable
{
	public function __construct($name, $closure, $options)
	{
		parent::__construct($name);
		
		$closure($this);
	}
}
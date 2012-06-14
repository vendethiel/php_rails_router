<?php
namespace Router;

class RoutableCollection extends NamedRoutable
{
	public function __construct($name, $closure)
	{
		parent::__construct($name);

		$this->shallow_path = '/' . $this->name . '/';
		$this->shallow_name = $this->name . '_';

		$closure($this);
	}
}
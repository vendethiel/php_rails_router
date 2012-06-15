<?php
namespace Router;

class RoutableCollection extends NamedRoutable
{
	/**
	 * Constructs a `Collection` routable
	 *
	 * {@inheritdoc}
	 */
	public function __construct($name, $closure)
	{
		parent::__construct($name);

		$this->shallow_path = '/' . $this->name . '/';
		$closure($this);
	}
}
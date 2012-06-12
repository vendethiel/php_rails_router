<?php
namespace Router;

abstract class NamedRoutable extends Routable
{
	protected $name;
	public function __construct($name, $closure = null, $options = null)
	{
		$this->name = $name;
	}
	
	public function match($find, $to = '', $as = null, $via = null)
	{
		if (!$to && strpos($find, '#') === false)
			$to = $this->name . '#' . $find;
			
		parent::match($find, $to, $as, $via);
	}
	
	public function getRoot()
	{
		throw new \RuntimeException('Not implemented');
	}
}
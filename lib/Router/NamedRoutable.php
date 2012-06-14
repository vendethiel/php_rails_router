<?php
namespace Router;

abstract class NamedRoutable extends Routable
{
	protected $name;
	/**
	 * constructs a named routable
	 *
	 * @param string $name Routable name
	 * @param closure $closure DSL to eval
	 */
	public function __construct($name, $closure = null)
	{
		$this->name = $name;
	}
	
	
	/**
	 * {@inheritdoc}
	 */
	static private function guessAs($to, $find = null)
	{
		//remove any unneeded slash
		$to = trim($to, '/');

		if (strpos($to, ':') !== false)
			return null;
		if (strpos($to, '/') === false)
			return static::guessControllerAsPattern($to, $find);

		return static::guessControllerAsPattern(str_replace('/', '_', $to), $find);
	}
	
	/**
	 * {@inheritdoc}
	 * Modified to guess controller based on the name given
	 */
	public function match($find, $to = '', $as = null, $via = null)
	{
		if (!$to && strpos($find, '#') === false)
			$to = $this->name . '#' . $find;
			
		parent::match($find, $to, $as, $via);
	}
	
	/**
	 * Overrides {Routable::getRoot()} to unallow it
	 * @throws \RuntimeException
	 */
	public function getRoot()
	{
		throw new \RuntimeException('Not implemented');
	}
}
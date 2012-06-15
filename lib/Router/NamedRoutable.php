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
	 * Modified to guess controller based on the name given
	 */
	public function match($find, $to = '', $as = null, $via = null)
	{
		if (!$to && strpos($find, '#') === false)
			$to = $this->name . '#' . $find;
			
		parent::match($find, $to, $as, $via);
	}

	/**
	 * {@inheritdoc}
	 */
	public function ns($ns, $closure)
	{
		$ns = new NamespacedRoutable($ns, $ns, $ns);
		$closure($ns);
		$this->addSubRoutes($ns);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function scope($scope, $closure)
	{
		$scope = new ScopedRoutable('', $scope, $scope);
		$closure($scope);
		$this->addSubRoutes($scope);
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function prefix($prefix, $closure)
	{
		$prefix = new PrefixedRoutable($prefix, '', '');
		$closure($prefix);
		$this->addSubRoutes($prefix);
	}

	/**
	 * Overrides {Routable::getRoot()} to unallow it
	 * @throws \RuntimeException
	 */
	public function getRoot()
	{
		throw new \RuntimeException('Not implemented');
	}
	
	/**
	 * Formats a NamedRoutable's as
	 */
	protected function formatAs($as)
	{
		return $this->name . '_' . $as;
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
}
<?php
namespace Router;

class PrefixedRoutable extends Routable
{
	/**
	 * Creates a `Resources` and allows to pass a DSL in
	 *
	 * @param string $name `Resources`'s name
	 * @param closure $closure optional DSL to eval
	 * @param array $options `Resources` options
	 */
	public function resources($name, $closure = null, $options = array())
	{
		$resources = new RoutableResources($name, $closure, $options);
		$this->addSubRoutes($resources->getRoutes());
	}
}
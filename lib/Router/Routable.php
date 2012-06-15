<?php
namespace Router;

class Routable extends AbstractRoutable
{
	protected $root;
	
	/**
	 * Creates a routable
	 *
	 * @param string $shallow_path Additional part of the part
	 * @param string $shallow_name Additional part of the route name
	 * @param string $sn Controller namespace
	 * @param array $constraints Inherited constraints
	 */
	public function __construct($shallow_path = '', $shallow_name = '', $ns = '', $constraints = array())
	{
		$shallow_path = trim($shallow_path, '/');
	
		$this->shallow_path = '' === $shallow_path ? '' : $shallow_path . '/';
		$this->shallow_name = '' === $shallow_name ? '' : $shallow_name . '_';
		$this->ns = $ns ? $ns . '\\' : '';
		$this->constraints = $constraints;
	}
	
	/**
	 * Changes the root
	 *
	 * @param string $to Controller action
	 * @param string $as Route name
	 */
	public function root($to, $as = null)
	{
		$root = array('find' => $this->shallow_path ? $this->shallow_path . '/' : '', 'to' => $to, 'as' => $as ?: self::guessAs($to), 'via' => array(Router::METHOD_GET));

		$this->routes[] = $root;
	}
	
	/**
	 * Adds a route
	 *
	 * @param string $find URL to find
	 * @param string $to Controller/action name, formatted as controller#action
	 * @param string $as Route name
	 * @param array $options Route options :
	 * 						- via		   : array of HTTP methods to access the route
	 * 						- constraints  : constraints for the route to be match
	 */
	public function match($find, $to = '', $as = null, $options = array())
	{
		if ('' === $to)
		{
			$to = $find;
			$find = '';
		}
		if (is_array($as))
		{
			$options = $as;
			$as = null;
		}
		
		if (isset($options['via']))
			$via = (array) $options['via'];
		else
			$via = Router::getHttpMethods();

		if (in_array(Router::METHOD_GET, $via))
			$as = null === $as ? self::guessAs($to, $find) : $as;
		else
			$as = '_' === $as ? '_' : trim($as ? $this->shallow_name . $as : '', '_');
		
		if (strpos($to, '#') === false)
		{ //Try to guess the controller name, by the first $find-path's part
			$find_parts = explode('/', $find);
			$to = $find_parts[0] . '#' . $to;
		}
		
		if (isset($options['constraints'])) //if we have constraints, they may override ours
			$constraints = array_merge($this->constraints, $options['constraints']);
		else //else, just take the inherited
			$constraints = $this->constraints;	
		
		$this->routes[] = array(
			'find' => trim($this->shallow_path . $find, '/'),
			'to' => ucfirst($to),
			'as' => in_array(Router::METHOD_GET, $via) ? ($as ? $as : static::formatAs($as)) : null,
			'via' => $via,
			'constraints' => $constraints,
		);
	}
	
	/**
	 * Alias of match(), but forces via => GET
	 *
	 * @param string $find URL to find
	 * @param string $to Controller/action name, formatted as controller#action
	 * @param string $as Route name
	 * @param array $options Route options :
	 * 						- via		   : array of HTTP methods to access the route
	 * 						- constraints  : constraints for the route to be match
	 */
	public function get($find, $to = '', $as = null, $opt = array())
	{
		if (is_array($as))
		{
			$opt = $as;
			$as = null;
		}
		
		$this->match($find, $to, $as, array_merge($opt, array('via' => Router::METHOD_GET)));
	}
	/**
	 * Alias of match(), but forces via => POST
	 *
	 * @param string $find URL to find
	 * @param string $to Controller/action name, formatted as controller#action
	 * @param string $as Route name
	 * @param array $options Route options :
	 * 						- via		   : array of HTTP methods to access the route
	 * 						- constraints  : constraints for the route to be match
	 */
	public function post($find, $to = '', $as = null, $opt = array())
	{
		if (is_array($as))
		{
			$opt = $as;
			$as = null;
		}
		
		$this->match($find, $to, $as, array_merge($opt, array('via' => Router::METHOD_POST)));
	}
	/**
	 * Alias of match(), but forces via => POST
	 *
	 * @param string $find URL to find
	 * @param string $to Controller/action name, formatted as controller#action
	 * @param string $as Route name
	 * @param array $options Route options :
	 * 						- via		   : array of HTTP methods to access the route
	 * 						- constraints  : constraints for the route to be match
	 */
	public function put($find, $to = '', $as = null, $opt = array())
	{
		if (is_array($as))
		{
			$opt = $as;
			$as = null;
		}
		
		$this->match($find, $to, $as, array_merge($opt, array('via' => Router::METHOD_PUT)));
	}
	/**
	 * Alias of ::put()
	 *
	 * @param string $find URL to find
	 * @param string $to Controller/action name, formatted as controller#action
	 * @param string $as Route name
	 * @param array $options Route options :
	 * 						- via		   : array of HTTP methods to access the route
	 * 						- constraints  : constraints for the route to be match
	 */
	public function patch($find, $to = '', $as = null, $opt = array())
	{
		$this->put($find, $to, $as, $opt);
	}
	/**
	 * Alias of match(), but forces via => DELETE
	 *
	 * @param string $find URL to find
	 * @param string $to Controller/action name, formatted as controller#action
	 * @param string $as Route name
	 * @param array $options Route options :
	 * 						- via		   : array of HTTP methods to access the route
	 * 						- constraints  : constraints for the route to be match
	 */
	public function delete($find, $to = '', $as = null, $opt = array())
	{
		if (is_array($as))
		{
			$opt = $as;
			$as = null;
		}
		
		$this->match($find, $to, $as, array_merge($opt, array('via' => Router::METHOD_DELETE)));
	}

	/**
	 * Adds a scope, meaning it adds the Scope name to the path and the route name
	 *
	 * @param string $scope Scope name
	 * @param closure $closure DSL to eval
	 */
	public function scope($scope, $closure)
	{
		$scope = new ScopedRoutable('', $scope, $scope);
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
		$prefix = new PrefixedRoutable($prefix, '', '');
		$closure($prefix);
		$this->addSubRoutes($prefix);
	}
	
	/**
	 * Adds a namespace, meaning it adds the Ns to the path, the route name and the controller name
	 *
	 * @param string $ns Namespace name
	 * @param closure $closure DSL to eval
	 */
	public function ns($ns, $closure)
	{
		$ns = new NamespacedRoutable($ns, $ns, $ns);
		$closure($ns);
		$this->addSubRoutes($ns);
	}
	
	/**
	 * @return string root url
	 */
	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * Must be overriden if needed
	 */
	protected function formatAs($as)
	{
		return $as;
	}
	
	/**
	 * Tries to guess the $as part of a match() call
	 *
	 * @param string $to Route controller/action part
	 * @param string $find Route path
	 * @return string the guessed "as"
	 */
	static private function guessAs($to, $find = null)
	{
		//remove any unneeded slash
		$to = trim($to, '/');

		if (strpos($to, ':') !== false)
			return null;
		if (strpos($to, '/') === false && '' != $find)
			return $find;

		return static::guessControllerAsPattern(str_replace('/', '_', $to), $find);
	}
	
	/**
	 * Tries to guess the $as part of a match() call, knowing we're in a controller
	 *
	 * @param string $to Route controller/action part
	 * @param string $find Route path
	 * @return string the guessed "as"
	 */
	static protected function guessControllerAsPattern($to, $find = null)
	{
		if (strpos($to, '#') === false)
			return $to . '#' . $find;
		list($controller, $action) = explode('#', $to);

		if ($action == 'index' || $action == 'show')
			return '_';

		return $action;
	}
}
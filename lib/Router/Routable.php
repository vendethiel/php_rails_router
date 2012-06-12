<?php
namespace Router;

class Routable extends AbstractRoutable
{
	protected $root;
	
	public function __construct($shallow_path = '', $shallow_name = '', $ns = '')
	{
		$shallow_path = trim($shallow_path, '/');
	
		$this->shallow_path = '' === $shallow_path ? '' : $shallow_path . '/';
		$this->shallow_name = '' === $shallow_name ? '' : $shallow_name . '_';
		$this->ns = $ns ? $ns . '\\' : '';
	}
	
	public function root($to, $as = null)
	{
		$root = array('find' => $this->shallow_path ? $this->shallow_path . '/' : '', 'to' => $to, 'as' => $as ?: self::guessAs($to), 'via' => array(Router::METHOD_GET));

		$this->routes[''] = $root;
	}
	public function match($find, $to = '', $as = null, $via = null)
	{
		if ('' === $to)
		{
			$to = $find;
			$find = '';
		}
		
		if (null === $via)
			$via = Router::getHttpMethods();
		else
			$via = (array) $via;

		$as = null;
		if (in_array(Router::METHOD_GET, $via))
			$as = null === $as ? self::guessAs($to) : $as;
		$as = '_' === $as ? '_' : trim($as ? $this->shallow_name . $as : '', '_');
		
		$this->routes[] = array('find' => trim($this->shallow_path . $find, '/'), 'to' => $this->ns . $to, 'as' => $as, 'via' => $via);
	}
	public function get($find, $to = '', $as = null)
	{ $this->match($find, $to, $as, Router::METHOD_GET); }
	public function post($find, $to = '', $as = null)
	{ $this->match($find, $to, $as, Router::METHOD_POST); }
	public function put($find, $to = '', $as = null)
	{ $this->match($find, $to, $as, Router::METHOD_PUT); }
	public function patch($find, $to = '', $as = null)
	{ $this->put($find, $to, $as); }
	public function delete($find, $to = '', $as = null)
	{ $this->match($find, $to, $as, Router::METHOD_DELETE); }
	
	public function getRoot()
	{
		return $this->root;
	}

	static private function guessAs($to)
	{
		//remove any unneeded slash
		$to = trim($to, '/');

		if (strpos($to, ':') !== false)
			return null;
		if (strpos($to, '/') === false)
			return static::guessControllerAsPattern($to);

		return static::guessControllerAsPattern(str_replace('/', '_', $to));
	}
	
	static protected function guessControllerAsPattern($to)
	{
		list($controller, $action) = explode('#', $to);

		if ($action == 'index' || $action == 'show')
			return '_';

		return $action;
	}
}
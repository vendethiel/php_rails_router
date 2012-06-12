<?php
require 'vendor/autoload.php';
function vardump(){echo'<pre>';$e=func_get_args();foreach($e as $a)var_dump($a);}
function vdump(){call_user_func_array('vardump',func_get_args());exit;}


$router = Router\Router::draw(function ($r)
{
	$r->resource('session');
	$r->ns('admin', function ($r)
	{
		$r->resources('articles', function ($r)
		{
			$r->resources('comments', function ($r)
			{
				$r->collection('love');
				$r->resources('votes');
			});
			
			$r->member('a', array('via' => Router\Router::METHOD_POST));
			$r->collection(array('do', 'something'));
		});
	});
	
	$r->get('products', 'products#index');
	$r->get('products/:id', 'products#show');
	$r->delete('products/:id', 'products#destroy');
	$r->match('products/:id', 'products#show', 'product_show');
	
	
	$r->root('products#index');
});
vdump($router->getRoutes());
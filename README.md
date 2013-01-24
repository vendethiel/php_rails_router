# Rails Router DSL for PHP
This repository is a port of the Rails Router DSL for PHP.


**Note:** You should not use or try anything with this project if you don't indent to shoot yourself soon enough


## Usage
Use `Router\Routes::draw` and give it a closure. You will be nesting closure, in order to mimicate the DSL.
The base routable will be injected into the closure as the argument, for example
```php
<?php //do require 'vendor/autoload'; and stuffs
Router\Router::draw(function ($r)
{
	//Here, your code !
});
```
### HTTP Verbs
The base routable object has http verbs as methods :
 * get
 * post
 * put
 * patch
 * delete
These http verbs maps to the method `match`, which has the following syntax :
`<?php $r->match('path/', 'controller#action', 'route_name', array('method'));`.
For example, `match('articles', 'articles#index', 'articles', Router\Router::METHOD_GET);`
The third article can be omitted, it'll default to the path with '/' replaced by '_'.
The fourth argument can either be an array, or just an unique value of the accepted method(s).
(note : the patch() method binds to put, but either are recognized)

### Namespaces
**XXX** : change merging strategy to make it work
You also have access to the method `ns` (namespace, but not allowed by PHP's parser - like if, or, ...).
The ns() method adds a _PHP Namespace_ and namespaces the route name.
As other extended methods, you have to pass a closure to it which will produce 
For example
```php
<?php Router\Router::draw(function ($r)
{
	$r->ns('admin', function ($r)
	{
		$r->get('articles', 'articles#index'); //we omit the third argument, as it'll be guessed to 'articles'
	});
});
```
The path will be /articles, but the controller name will be admin\articles#index and the helper will be admin_articles

### Resources (plural)
You can create CRUD resources using the method `resources`.
The method will generate 3 collection methods : index, new, create and 4 members method : show, edit, update destroy.
You can limit method generation by passing 'only' or 'except' in the `$options` array (optional third parameter).
The closure can be used to generate methods, nesting in `collection` and  `member`.
For example :
```php
<?php Router\Router::draw(function ($r)
{
	$r->resources('articles', function ($r)
	{
		$r->member(function ($r)
		{ //binds to /articles/:id/
			$r->get('sell'); //full path : /articles/:id/sell
		});
		
		$r->collection(function ($r)
		{ //binds to /articles/
			$r->get('clear'); //full path : /articles/clear
		});
	}, array('except' => 'destroy'));
});
```
You can also pass directly path(s) into `member` and `collection` and give the via in the options array :
```php
<?php Router\Router::draw(function ($r)
{
	$r->resources('articles', function ($r)
	{
		//you can also pass a single member, not an array
		$r->member('sell');
		
		//you can pass the array as the second parameter, since there's no closure
		$r->collection(array('reload_list', 'enforce'), array('via' => array(Router\Router::POST, Router\Router::PUT)));
	});
});
```

### Resource (singular)
You can create CRUD resource using the method `resource`.
This is similar to the method `resources` and has the same method, but there's no :id parameter and it binds to collection() directly
For example :
```php
<?php Router\Router::draw(function ($r)
{
	$r->resource('profile'); //generate singular CRUD resource under /profile/
	$r->resource('session', function ($r)
	{ //binds to /session/ - equivalent to collection() in plural resources
		$r->get('list'); //full path : /session/list
	});
});
```

### Nesting
You can of course nest all these methods, to create a powerful routing system with that powerful DSL!
This is a complicated example :
```php
<?php $router = Router\Router::draw(function ($r)
{
	$r->resource('session', function ($r)
	{ //shallow prefix : /session/
		$r->get('all'); //full path : /session/all
		$r->ns('admin', function ($r)
		{ //no shall prefix difference, only namespaces route name and controller
			$r->resources('articles', function ($r)
			{ //binds to /session/articles
			  //route name is admin_article(s)_
			  //route controller is admin\article
				$r->member(function ($r)
				{ //binds to /session/articles/:id/
					$r->get('publish'); //full path : /session/articles/:id/publish
					$r->resource('review'); //binded to /session/articles/:id/review/*, like any singuler resource
				});
			});
		});
	});
});
```

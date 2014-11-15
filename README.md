Tres router
===========

This is the router package used for [Tres Framework](https://github.com/tres-framework/Tres). 
This is a stand-alone package, which means that it can be used without the framework.

A router generally forwards you to something. In this case, we take the URI, match it with a 
list and do something based on that. What you want to do with the routes is up to you.

This technique adds a degree of separation between the files used to generate a webpage and the 
URL that is presented to the world. An addition to that is not only that it is search engine friendly,
but also that it's prettier for humans like you and me.

## Requirements
- PHP 5.4 or greater.
- A web server with .htaccess support.
- The mod_rewrite module for .htaccess.

## Examples
```php
<?php

Route::setConfig([
    // Route's basepath.
    'root' => __DIR__,
    
    'controllers' => [
        // The namespace for all controllers.
        'namespace' => 'controllers',
        
        // The directory the controllers are stored in.
        'dir' => dirname(__DIR__)
    ]
]);

Route::get('/', function(){
    include('views/homepage.php');
});

Route::get('/blog/posts/:id/', [
    'controller' => 'PostController',
    'method' => 'getPost'
]);

Route::post('/newsletter', [
    'controller' => 'NewsletterController',
    'method' => 'thankUser',
    'args' => [
        'name' => 'Ped Zed',
        'email' => 'pedzed@example.com'
    ]
]);

Route::register(['GET', 'POST'], '/contact', function(){
    include('views/thanks.php');
});

Route::notFound([
    'controller' => 'ErrorController',
    'method' => 'notFound'
]);

Route::dispatch();
```
### Aliasing
```php
Route::get('/alias/test', [
    'alias' => 'test',
    'controller' => 'ExampleController',
    'method' => 'renderPage'
]);

Route::get('/about', [
    'alias' => 'about',
    
    function(){
        echo '<h1 style="font-family:Calibri, sans-serif;">Tres router</h1>';
        echo '<pre>', print_r(\packages\Tres\router\PackageInfo::get()), '</pre>';
    }
]);

echo '<a href="'.URL::route('test').'">Test</a><br />';
echo '<a href="'.URL::route('about').'">About</a><br />';
```

*This package is inspired by [Macaw router](https://github.com/NoahBuscher/Macaw).*

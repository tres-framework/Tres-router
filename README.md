Tres router
===========

This is the router package used for [Tres Framework](https://github.com/tres-framework/Tres). 
This is a stand-alone package, which means that it can be used without the framework.

## Intro
A router generally forwards you to something. In this case, we take the URI, match it with a 
list and do something based on that. What you want to do with the URI's is up to you.

This technique adds a degree of separation between the files used to generate a webpage and the 
URL that is presented to the world. An addition to that is not only that it is search engine friendly,
but also that it's prettier for humans.

## Requirements
- PHP 5.4 or greater.
- A web server with .htaccess support.
- The mod_rewrite module for .htaccess.

## Examples
```php
<?php

Route::setRoot(__DIR__);
Route::setControllerNamespace('controllers');

Route::get('/', function(){
    include('views/homepage.php');
});

Route::get('/path/to/x/y/z/', [
    'controller' => 'ExampleController',
    'method' => 'exampleMethod'
]);

Route::post('newsletter', [
    'controller' => 'NewsletterController',
    'method' => 'thankUser',
    'args' => [
        'name' => 'Ped Zed',
        'email' => 'pedzed@example.com'
    ]
]);

Route::register(['GET', 'POST'], 'contact', function(){
    include('views/thanks.php');
});

Route::dispatch();
```

*This package is inspired by [Macaw router](https://github.com/NoahBuscher/Macaw).*

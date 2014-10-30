Tres router
===========

This is the configuration package used for the [Tres Framework](https://github.com/tres-framework/Tres). 
It can be used without the framework core.

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

*Inspired by [Macaw router](https://github.com/NoahBuscher/Macaw/blob/master/Macaw.php).*

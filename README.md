Tres router (unstable) beta
===========

This is the configuration package used for the [Tres Framework](https://github.com/tres-framework/Tres). 
It can be used without the main framework.

## Examples
```php
Route::get('/', function(){
    include('views/homepage.php');
});
Route::get('/path/to/x/y/z/', function(){
    include('views/xyz.php');
});
Route::post('newsletter', function(){
    include('views/thanks.php');
});
Route::register(['GET', 'POST'], 'contact', function(){
    include('views/thanks.php');
});
Route::dispatch();
```

*Inspired by [Macaw router](https://github.com/NoahBuscher/Macaw/blob/master/Macaw.php)*

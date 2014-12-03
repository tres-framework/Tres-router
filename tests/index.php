<?php

use Tres\router\RouteException;
use Tres\router\ConfigException;
use Tres\router\Config;

ini_set('display_errors', 1);
error_reporting(-1);

define('PUBLIC_URL', 'http://tres-router.dev');

$dirs = [
    dirname(__DIR__).'/src/',
    dirname(__DIR__).'/tests/'
];

spl_autoload_register(function($class) use ($dirs){
    foreach($dirs as $dir){
        $file = $dir.str_replace('\\', '/', $class.'.php');
        
        if(is_readable($file)){
            require_once($file);
            break;
        }
    }
});

class_alias('Tres\router\Redirect', 'Redirect');
class_alias('Tres\router\Route', 'Route');
class_alias('Tres\router\URL', 'URL');

try {
    Route::setConfig([
        'root' => __DIR__,
        
        'controllers' => [
            'namespace' => 'controllers',
            'dir' => __DIR__.'/controllers'
        ]
    ]);
} catch(ConfigException $e){
    echo $e->getMessage();
}

try {
    require_once('routes.php');
} catch(RouteException $e){
    echo $e->getMessage();
}


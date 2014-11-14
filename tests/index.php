<?php

use packages\Tres\router\RouteException;
use packages\Tres\router\ConfigException;
use packages\Tres\router\Config;

ini_set('display_errors', 1);
error_reporting(-1);

define('PUBLIC_DIR', 'http://tres-router.dev');

spl_autoload_register(function($class){
    $file = dirname(__DIR__).'/'.str_replace('\\', '/', $class.'.php');
    
    if(is_readable($file)){
        require_once($file);
    } else {
        if(is_file($file)){
            die($file.' is not readable.');
        } else {
            die($file.' does not exist.');
        }
    }
});

class_alias('packages\Tres\router\Route', 'Route');
class_alias('packages\Tres\router\URL', 'URL');

try {
    Route::setConfig(
        new Config([
            'root' => __DIR__,
            'controllers' => [
                'namespace' => 'tests\\controllers',
                'dir' => dirname(__DIR__)
            ]
        ])
    );
} catch(ConfigException $e){
    echo $e->getMessage();
}

try {
    require_once('routes.php');
} catch(RouteException $e){
    echo $e->getMessage();
}


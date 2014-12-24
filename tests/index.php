<?php

use Tres\router\Config;

ini_set('display_errors', 1);
error_reporting(-1);

set_exception_handler(function($e){
    echo '<br /><b>Exception:</b> '.$e->getMessage();
});

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

Route::$config = [
    'root' => __DIR__,
    'default_controller_namespace' => 'controllers'
];

require_once('routes.php');

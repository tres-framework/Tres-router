<?php

use packages\Tres\router\Route;
use packages\Tres\router\PackageInfo;

require_once('../packages/Tres/router/Route.php');
require_once('../packages/Tres/router/PackageInfo.php');


Route::get('/', function(){
    echo '<h1>ROOT! :D</h1>';
    echo 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Omnis, quisquam.';
});

Route::get('/form/get/', function(){
    echo '<h1>GET</h1>';
    echo 'You are now GETting.<br />';
    echo '<pre>', print_r($_GET), '</pre>';
    
    include('form.php');
});
Route::post('/form/get/post', function(){
    echo '<h1>POST</h1>';
    echo 'Posted.<br />';
    echo '<pre>', print_r($_POST), '</pre>';
});

Route::get('/about', function(){
    echo '<h1>About?</h1>';
    echo '<pre>', print_r(PackageInfo::get()), '</pre>';
});

Route::dispatch();

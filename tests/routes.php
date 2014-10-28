<?php

use packages\Tres\router\Route;
use packages\Tres\router\PackageInfo;


Route::get('/', function(){
    echo '<h1>ROOT! :D</h1>';
    echo 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Omnis, quisquam.';
});

Route::register(['GET', 'POST'], 'multi-request', function(){
    echo (isset($_POST) && !empty($_POST)) ? 'POST' : 'GET', '<br />';
    ?>
    <form method="POST">
        <input type="submit" name="submit" />
    </form>
    <?php
});

Route::get('/form/get/', function(){
    echo '<h1>GET</h1>';
    echo 'You are now GETting.<br />';
    
    include('form.php');
});
Route::post('/form/get/post/', function(){
    echo '<h1>POST</h1>';
    echo 'Posted:';
    //echo '<pre>', print_r($_POST), '</pre>';
});

Route::get('/about', function(){
    echo '<h1>About?</h1>';
    echo '<pre>', print_r(PackageInfo::get()), '</pre>';
});

/*Route::error('/error-404', 404, function(){
    echo '<h1>Error 404 ;(</h1>';
    echo '<p>Page not found.</p>';
});*/

Route::dispatch();

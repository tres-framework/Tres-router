<?php

Route::get('/', [
    'controller' => 'HomeController',
    'method' => 'exampleMethod'
]);

Route::get('/paramtest', [
    'controller' => 'HomeController',
    'method' => 'exampleMethod2',
    'args' => [
        'first' => 1,
        'second' => '2',
        'third' => 'three'
    ]
]);

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
    echo '<pre>', print_r($_POST), '</pre>';
});

Route::get('/about', function(){
    echo '<h1 style="font-family:Calibri, sans-serif;">About Tres router</h1>';
    echo '<pre>', print_r(\packages\Tres\router\PackageInfo::get()), '</pre>';
});

Route::dispatch();

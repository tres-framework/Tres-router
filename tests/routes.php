<?php

Route::import('grouping-routes.php');

Route::get('/', [
    'alias' => 'home',
    function(){
        echo "URL::route('gtest.home') = ".URL::route('gtest.home').'<br />';
        echo "URL::route('gtest3.nested.alias-test') = ".URL::route('gtest3.nested.alias-test').'<br />';
    }
]);

Route::get('/about', [
    'alias' => 'about',
    function(){
        echo '<h1 style="font-family:Calibri, sans-serif;">About Tres router</h1>';
        echo '<pre>', print_r(Tres\router\PackageInfo::get()), '</pre>';
    },
    function(){
        echo 'Won\'t run.';
    }
]);

Route::get('/users/:username/', [
    'uses' => 'UserController@getProfile',
    'args' => [
        'test'
    ]
]);

Route::get('/posts/:id/:title', function($id, $title){
    ?>
    <h1>#<?php echo $id; ?> - <?php echo $title; ?></h1>
    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
    quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
    consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
    cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non
    proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
    <?php
});

Route::get('/paramtest', [
    'uses' => 'HomeController@exampleMethod2',
    'args' => [
        'first' => 1,
        'second' => '2',
        'third' => 'three'
    ],
    'alias' => 'param'
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

Route::get('/url/tests/home', function(){
    echo URL::route('home');
});

Route::get('/url/tests/about', function(){
    echo '<a href="'.URL::route('about').'">'.URL::route('about').'</a>';
});

Route::get('/url/tests/paramtest', function(){
    echo '<a href="'.URL::route('param').'">'.URL::route('param').'</a>';
});

Route::notFound([
    'uses' => 'ErrorController@notFound'
]);

Route::dispatch();

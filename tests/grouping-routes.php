<?php

Route::group(function(){
    
    Route::get('/global-test', function(){
        echo 'Global namespace group test.';
    });
    
});

Route::group('gtest', function(){
    
    Route::get('/', [
        'alias' => 'home',
        function(){
            echo 'Group test @ gtest: home';
        }
    ]);
    
});

Route::group([
    'prefix' => 'gtest2.separated.with.dot'
], function(){
    
    Route::group('nested', function(){
        
        Route::get('/', function(){
            echo 'You are now at /gtest2/separated/with/dot/nested/.';
        });
        
    });
    
    Route::get('/', [
        function(){
            echo 'Group test @ gtest2.separated.with.dot';
        }
    ]);
    
});

Route::group('gtest3', function(){
    
    Route::group('nested', function(){
        
        Route::get('/test', [
            'alias' => 'alias-test',
            function(){
                echo 'You are now at /gtest3/nested/test.';
            }
        ]);
        
    });
    
});

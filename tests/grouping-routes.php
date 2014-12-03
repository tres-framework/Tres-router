<?php

Route::group('gtest', function(){
    
    Route::get('/', [
        function(){
            echo 'Group test';
        }
    ]);
    
});

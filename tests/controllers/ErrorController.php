<?php

namespace tests\controllers {
    
    class ErrorController extends BaseController {
        
        public function notFound(){
            header('HTTP/1.0 404 Not Found');
            echo '<h1>Error 404 - Not Found</h1>';
            echo '<p>The page could not be found. :(</p>';
        }
        
    }
    
}

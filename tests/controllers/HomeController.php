<?php

namespace controllers {
    
    class HomeController extends BaseController {
        
        public function __construct(){
            parent::__construct();
            
            echo 'Constructed HomeController.<br />';
        }
        
        public function exampleMethod(){
            echo 'Called exampleMethod().<br />';
        }
        
        public function exampleMethod2(){
            echo 'func_get_args():<br />';
            echo '<pre>', print_r(func_get_args()), '</pre><br />';
        }
        
    }
    
}

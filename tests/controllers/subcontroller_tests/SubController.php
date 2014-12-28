<?php

namespace controllers\subcontroller_tests {
    
    use controllers\BaseController;
    
    class SubController extends BaseController {
        
        public function __construct(){
            parent::__construct();
            
            echo 'Constructed SubController.<br />';
        }
        
        public function testMethod(){
            echo 'Called testMethod().<br />';
        }
        
    }
    
}

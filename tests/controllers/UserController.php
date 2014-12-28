<?php

namespace controllers {
    
    class UserController extends BaseController {
        
        protected $_users = [
            1 => [
                'name' => 'user one',
                'username' => 'user1',
                'email' => 'user1@example.com'
            ],
            2 => [
                'name' => 'user two',
                'username' => 'user2',
                'email' => 'user2@example.com'
            ],
            17 => [
                'name' => 'user 17',
                'username' => 'user17',
                'email' => 'user17@example.com'
            ]
        ];
        
        public function __construct(){
            parent::__construct();
            
            echo 'Constructed UserController.<br />';
        }
        
        public function getProfile($id){
            if(isset($this->_users[$id])){
                echo '<pre>', print_r($this->_users[$id]), '</pre>';
            } else {
                // Error 404
                echo 'User not found.';
            }
        }
        
    }
    
}

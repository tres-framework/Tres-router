<?php

namespace packages\Tres\router {
    
    use Exception;
    
    class URLException extends Exception implements ExceptionInterface {}
    
    class URL {
        
        // Prevents instantiation.
        private function __construct(){}
        private function __clone(){}
        
        /**
         * Returns the absolute path from a route's alias.
         * 
         * @param  string $alias The route's alias.
         * @return string
         */
        public static function route($alias){
            self::_checkConstants();
            
            foreach(Route::getList() as $k => $route){
                if(is_array($route['options']) &&
                   isset($route['options']['alias']) &&
                   $alias === $route['options']['alias']
                ){
                    return PUBLIC_URL.'/'.trim($route['route'], '/');
                }
            }
            
            throw new URLException('Route with alias "'.$alias.'" not found.');
        }
        
        /**
         * Checks if the necessary constants are defined.
         */
        protected static function _checkConstants(){
            if(!defined('PUBLIC_URL')){
                throw new URLException('No constant "PUBLIC_URL" defined.');
            }
        }
        
    }
    
}

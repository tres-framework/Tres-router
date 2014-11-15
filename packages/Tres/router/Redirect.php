<?php

namespace packages\Tres\router {
    
    /**
     * Redirect class.
     */
    class Redirect {
        
        // Prevents instantiation.
        private function __construct(){}
        private function __clone(){}
        
        /**
         * Redirects and kills the page.
         * 
         * @param string $uri The path to redirect.
         */
        public static function to($uri){
            header('Location: '.$uri);
            die();
        }
        
        /**
         * Redirects to the specified route.
         * 
         * @param string $alias The route's alias.
         */
        public static function route($alias){
            self::to(URL::route($alias));
        }
        
    }
    
}

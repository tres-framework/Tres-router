<?php

namespace packages\Tres\router {
    
    final class Config {
        
        /**
         * The configuration.
         * 
         * @var array
         */
        protected $_config = [];
        
        /**
         * Sets the config.
         * 
         * @param array $config
         */
        public function __construct(array $config){
            $this->_config = $config;
        }
        
        /**
         * Gets the config.
         * 
         * @return array
         */
        public function get(){
            return $this->_config;
        }
        
    }
    
}

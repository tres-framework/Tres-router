<?php

namespace packages\Tres\router {
    
    use Exception;
    
    class HTTPRouteException extends Exception {}
    class RouteException extends Exception {}
    
    class Route {
        
        /**
         * The route paths.
         * 
         * @var array
         */
        protected static $_routes = [];
        
        /**
         * The route HTTP requests.
         * 
         * @var array
         */
        protected static $_requests = [];
        
        /**
         * The accepted HTTP requests.
         * 
         * @var array
         */
        protected static $_acceptedRequests = [
            'GET',
            'POST'
        ];
        
        /**
         * The route actions.
         * 
         * @var array
         */
        protected static $_actions = [];
        
        /**
         * Registers a route with a GET request.
         * 
         * @param string         $route  The route path.
         * @param callable|array $action The action.
         */
        public static function get($route, $action){
            self::register('GET', $route, $action);
        }
        
        /**
         * Registers a route with a POST request.
         * 
         * @param string         $route  The route path.
         * @param callable|array $action The action.
         */
        public static function post($route, $action){
            self::register('POST', $route, $action);
        }
        
        /**
         * Registers a route.
         * 
         * @param  string         $request The HTTP request.
         * @param  string         $route   The route path.
         * @param  callable|array $action  The route action.
         */
        public static function register($request, $route, $action){
            if(!is_string($route)){
                throw new RouteException('Route path must be a string.');
            }
            
            $requests = is_array($request) ? $request : [$request];
            
            foreach($requests as $request){
                if(!in_array($request, self::$_acceptedRequests)){
                    throw new HTTPRouteException('The '.$request.' HTTP request is not supported.');
                }
                
                self::$_routes[] = $route;
                self::$_requests[] = $request;
                self::$_actions[] = $action;
            }
        }
        
        /**
         * Gets the current URI.
         * 
         * @return string
         */
        protected static function _getURI(){
            return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        }
        
        /**
         * Processes the routes.
         */
        public static function dispatch(){
            $uri = trim(self::_getURI(), '/');
            $routes = array_map('trim', self::$_routes, array_fill(0, count(self::$_routes), '/'));
            $request = $_SERVER['REQUEST_METHOD'];
            
            $routeMatched = false;
            
            if(in_array($uri, $routes)){
                $matchedRoutes = array_keys($routes, $uri);
                
                foreach($matchedRoutes as $route){
                    if(self::$_requests[$route] === $request){
                        $routeMatched = true;
                        
                        if(is_array(self::$_actions[$route])){
                            // array
                        } else if(is_callable(self::$_actions[$route])){
                            call_user_func(self::$_actions[$route]);
                        } else {
                            throw new RouteException('Second argument is not an array, nor a callback.');
                        }
                    }
                }
            }
        }
        
    }
    
}

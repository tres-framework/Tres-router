<?php

namespace packages\Tres\router {
    
    use Exception;
    
    class HTTPRouteException extends Exception {}
    class RouteException extends Exception {}
    
    class Route {
        
        /**
         * The project root.
         * 
         * @var string
         */
        protected static $_root = '';
        
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
         * The namespace of the controllers.
         * 
         * @var string
         */
        protected static $_controllerNamespace = '';
        
        /**
         * The controller extension.
         */
        const CONTROLLER_EXT = '.php';
        
        /**
         * The project root.
         * 
         * @param string $path
         */
        public static function setRoot($path){
            self::$_root = $path;
        }
        
        /**
         * The namespace for the controllers.
         * 
         * @param string $ns
         */
        public static function setControllerNamespace($ns = 'controllers'){
            self::$_controllerNamespace = $ns;
        }
        
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
         * 
         * @return bool True on success.
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
                            $args = [];
                            
                            extract(self::$_actions[$route]);
                            
                            if(isset($controller, $method)){
                                $controllerName = $controller;
                                $controller = self::$_controllerNamespace.'\\'.$controllerName;
                                
                                $controllerFile  = self::$_root.'/';
                                $controllerFile .= str_replace('\\', '/', $controller);
                                $controllerFile .= self::CONTROLLER_EXT;
                                
                                if(!is_readable($controllerFile)){
                                    throw new RouteException('Controller "'.$controllerName.'" is not found.');
                                }
                                
                                if(!method_exists($controller, $method)){
                                    throw new RouteException(
                                        'Method "'.$method.'" does not exist in the "'.$controllerName.'" controller.'
                                    );
                                }
                                
                                call_user_func_array([
                                    new $controller(),
                                    $method
                                ], $args);
                                
                                return true;
                            } else {
                                throw new RouteException('Routes require at least a controller and a method.');
                            }
                        } else if(is_callable(self::$_actions[$route])){
                            return call_user_func(self::$_actions[$route]);
                        } else {
                            throw new RouteException('Second argument is not an array, nor a callback.');
                        }
                    }
                }
            }
            
            throw new RouteException('Something went wrong.');
        }
        
    }
    
}

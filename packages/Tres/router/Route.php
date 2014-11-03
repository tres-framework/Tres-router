<?php

namespace packages\Tres\router {
    
    use Exception;
    use packages\Tres\router\Config;
    
    class HTTPRouteException extends Exception {}
    class RouteException extends Exception {}
    
    class Route {
        
        /**
         * The router configuration.
         * 
         * @var string
         */
        protected static $_config = [];
        
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
         * The HTTP request.
         * 
         * @var array
         */
        protected static $_request = '';
        
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
        
        public static function setConfig(Config $config){
            self::$_config = $config->get();
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
            $root = str_replace('\\', '/', self::$_config['root']);
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = $_SERVER['DOCUMENT_ROOT'].$uri;
            $uri = str_replace($root, '', $uri);
            
            return $uri;
        }
        
        /**
         * Processes the routes.
         * 
         * @return bool True on success.
         */
        public static function dispatch(){
            $uri = self::_getURI();
            self::$_request = $_SERVER['REQUEST_METHOD'];
            $routes = [];
            $routeMatched = false;
            
            foreach(self::$_routes as $route){
                $route = '/'.ltrim($route, '/');
                $route = rtrim($route, '/').'/';
                $routes[] = $route;
            }
            
            // Static URL
            if(in_array($uri, $routes)){
                $matchedRoutes = array_keys($routes, $uri);
                
                foreach($matchedRoutes as $route){
                    $routeMatched = self::_run($route);
                }
                
                if($routeMatched){
                    return true;
                }
            } else {
                // Dynamic URL
                
                $splitURI = explode('/', trim($uri, '/'));
                
                foreach(self::$_routes as $routeKey => $route){
                    // Continues to next loop if the placeholder identifier is not found.
                    if(strpos($route, ':') === false){
                        continue;
                    }
                    
                    $splitRoute = explode('/', trim($route, '/'));
                    
                    if($args = self::_getArgs($splitRoute, $splitURI)){
                        $routeMatched = self::_run($routeKey, $args);
                    }
                    
                    if($routeMatched){
                        return true;
                    }
                }
            }
            
            // TODO: Change to error 404.
            throw new RouteException('Something went wrong.');
        }
        
        /**
         * Finds the correct HTTP request and runs the route.
         * 
         * @param  int   $routeKey The key of the route.
         * @param  array $args     (Optional) The arguments to pass to the route.
         * @return mixed
         */
        public static function _run($routeKey, $args = []){
            if(self::$_requests[$routeKey] === self::$_request){
                if(is_array(self::$_actions[$routeKey])){
                    extract(self::$_actions[$routeKey]);
                    
                    if(isset($controller, $method)){
                        $controllerName = $controller;
                        $controller = self::$_config['controllers']['namespace'].'\\'.$controllerName;
                        
                        $controllerFile  = self::$_config['controllers']['dir'].'/';
                        $controllerFile .= str_replace('\\', '/', $controller);
                        $controllerFile .= '.php';
                        
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
                } else if(is_callable(self::$_actions[$routeKey])){
                    call_user_func_array(self::$_actions[$routeKey], $args);
                    
                    return true;
                } else {
                    throw new RouteException('Second argument is not an array, nor a callback.');
                }
            }
        }
        
        /**
         * Checks the URI and gets the arguments.
         * 
         * @param  array      $splitRoute The route array to check from.
         * @param  array      $splitURI   The path array to check from.
         * @return array|bool             Returns the parameters on success.
         *                                Returns false on failure.
         */
        protected static function _getArgs(array $splitRoute, array $splitURI){
            // Checks if all parameters are set and checks if the first part
            // of path matches recursively.
            if(count($splitURI) == count($splitRoute)){
                if(current($splitRoute) === current($splitURI)){
                    unset($splitRoute[array_keys($splitRoute)[0]]);
                    unset($splitURI[array_keys($splitURI)[0]]);
                    reset($splitRoute);
                    reset($splitURI);
                    
                    return self::_getArgs($splitRoute, $splitURI);
                } else {
                    $firstSplitRoute = reset($splitRoute);
                    
                    // Checks if it really starts with the placeholder identifier.
                    if(isset($firstSplitRoute[0]) && $firstSplitRoute[0] === ':'){
                        // Creates key value pairs
                        // ":placeholder" and "value" become ['placeholder' => 'value']
                        $values = [];
                        
                        foreach($splitRoute as $k => $singleSplitRoute){
                            if(isset($splitURI[$k])){
                                $values[str_replace(':', '', $singleSplitRoute)] = $splitURI[$k];
                            }
                        }
                        
                        return $values;
                    }
                }
            }
            
            return false;
        }
        
    }
    
}

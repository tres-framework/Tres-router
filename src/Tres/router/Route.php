<?php

namespace Tres\router {
    
    use Exception;
    use ReflectionClass;
    use Tres\router\Config;
    
    class HTTPRouteException extends Exception implements ExceptionInterface {}
    class RouteException extends Exception implements ExceptionInterface {}
    
    class Route {
        
        /**
         * The router configuration.
         * 
         * @var string
         */
        public static $config = [];
        
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
         * @var string
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
         * The route options.
         * 
         * @var array
         */
        protected static $_options = [];
        
        /**
         * The name/suffix used to identify a Not Found route.
         */
        const NOT_FOUND = '_404';
        
        /**
         * Registers a route with a GET request.
         * 
         * @param string         $route   The route path.
         * @param callable|array $options The route options.
         */
        public static function get($route, $options){
            self::register('GET', $route, $options);
        }
        
        /**
         * Registers a route with a POST request.
         * 
         * @param string         $route   The route path.
         * @param callable|array $options The route options.
         */
        public static function post($route, $options){
            self::register('POST', $route, $options);
        }
        
        /**
         * Registers a Not Found route.
         * 
         * @param callable|array $options The route options.
         */
        public static function notFound($options){
            self::register('GET', self::NOT_FOUND, $options);
        }
        
        /**
         * Registers a route with its information (request, path, options).
         * 
         * @param string|array   $request The HTTP request(s).
         * @param string|int     $route   The route path.
         * @param callable|array $options The route options.
         */
        public static function register($request, $route, $options){
            if($route !== self::NOT_FOUND && !is_string($route)){
                throw new RouteException('Route path must be a string.');
            }
            
            $backtraces = debug_backtrace();
            $prefix = '';
            
            // Gets group info if available.
            foreach(array_reverse($backtraces) as $backtrace){
                if(isset($backtrace['function']) && $backtrace['function'] === 'group'){
                    if(is_string($backtrace['args'][0])){
                        $prefix .= $backtrace['args'][0].'.';
                    } else if(is_array($backtrace['args'][0])){
                        $groupOptions = $backtrace['args'][0];
                        
                        if(isset($groupOptions['prefix'])){
                            $prefix .= $groupOptions['prefix'].'.';
                        }
                        
                        // TODO: Add filters (https://github.com/tres-framework/Tres-router/issues/8)
                    }
                }
            }
            
            $routePrefix = str_replace('.', '/', $prefix);
            $routePrefix = rtrim($routePrefix, '/');
            $requests = is_array($request) ? $request : [$request];
            
            foreach($requests as $request){
                if(!in_array($request, self::$_acceptedRequests)){
                    throw new HTTPRouteException('The '.$request.' HTTP request is not supported.');
                }
                
                if(is_array($options)){
                    if(isset($options['alias'])){
                        $options['alias'] = $prefix.$options['alias'];
                    }
                    
                    if(!isset($options['namespace']) && isset($groupOptions['namespace'])){
                        $options['namespace'] = $groupOptions['namespace'];
                    }
                }
                
                switch($route){
                    case self::NOT_FOUND:
                        $route = $prefix.self::NOT_FOUND;
                        self::$_routes[$route] = $route;
                        self::$_requests[$route] = $request;
                        self::$_options[$route] = $options;
                    break;
                    
                    default:
                        self::$_routes[] = $routePrefix.$route;
                        self::$_requests[] = $request;
                        self::$_options[] = $options;
                    break;
                }
            }
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
            
            foreach(self::$_routes as $k => $route){
                // Continues to next loop if the key doesn't end with the value of self::NOT_FOUND.
                if(substr($k, -strlen(self::NOT_FOUND)) === self::NOT_FOUND){
                    continue;
                }
                
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
            } else { // Dynamic URL
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
                }
            }
            
            if(!$routeMatched){
                if(!isset(self::$_routes[self::NOT_FOUND])){
                    self::notFound(function(){
                        header('HTTP/1.0 404 Not Found');
                        echo '<p>Not Found (404).</p>';
                    });
                }
                
                self::_run(self::NOT_FOUND); // TODO: Support grouped Not Found's.
            }
            
            return $routeMatched;
        }
        
        /**
         * Imports routes from another file.
         * 
         * @param string $file The path to the file.
         */
        public static function import($file){
            if(is_readable($file)){
                require_once($file);
            } else {
                throw new RouteException('Failed to import route. Does it exist?');
            }
        }
        
        /**
         * Used to group routes together. Checks there are no problems with 
         * the provided input.
         */
        public static function group(){
            $args = func_get_args();
            
            if(count($args) === 1){
                if(!is_callable($args[0])){
                    throw new RouteException('The first (and only) argument must be callable.');
                }
            } else if(count($args) === 2){
                list($options, $callable) = $args;
                
                if(!is_callable($callable)){
                    throw new RouteException('The second argument must be callable.');
                }
                
                if(!is_string($options) && !is_array($options)){
                    throw new RouteException('The first argument\'s type is invalid.');
                }
                
                call_user_func($callable);
            } else {
                throw new RouteException('The supplied amount of arguments is invalid.');
            }
        }
        
        /**
         * Gets a list of routes including its request and options.
         * 
         * @return array
         */
        public static function getList(){
            $list = [];
            
            foreach(self::$_routes as $k => $route){
                $list[$k] = [
                    'route' => $route,
                    'request' => self::$_requests[$k],
                    'options' => self::$_options[$k]
                ];
            }
            
            return $list;
        }
        
        /**
         * Gets the current URI.
         * 
         * @return string
         */
        protected static function _getURI(){
            $root = str_replace('\\', '/', self::$config['root']);
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$uri;
            $uri = str_replace($root, '', $uri);
            return $uri;
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
        
        /**
         * Finds the correct HTTP request and runs the route.
         * 
         * @param  int   $routeKey The key of the route.
         * @param  array $args     (Optional) The arguments to pass to the route.
         * 
         * @return bool
         */
        protected static function _run($routeKey, $args = []){
            if(self::$_requests[$routeKey] === self::$_request){
                $options = self::$_options[$routeKey];
                
                if(is_callable($options)){
                    call_user_func_array($options, $args);
                    return true;
                } else if(is_array($options)){
                    if(isset($options['args'])){
                        if(!is_array($options['args'])){
                            throw new RouteException('The "args" argument must be an array.');
                        }
                        
                        $args = array_merge($args, $options['args']);
                    }
                    
                    if(isset($options['uses'])){
                        if(empty($options['uses'])){
                            throw new RouteException('The "uses" argument cannot be empty.');
                        }
                        
                        $options['uses'] = explode('@', $options['uses'], 2);
                        $method = (isset($options['uses'][1])) ? $options['uses'][1] : null;
                        
                        if(isset($options['namespace'])){
                            $namespace = $options['namespace'];
                        } else {
                            $namespace = self::$config['default_controller_namespace'];
                        }
                        
                        $controller = $options['uses'][0];
                        $controller = $namespace.'\\'.$controller;
                        
                        if(!class_exists($controller)){
                            throw new RouteException('Controller "'.$controller.'" is not found.');
                        }
                        
                        if(!isset($method)){
                            $class = new ReflectionClass($controller);
                            $class->newInstanceArgs($args);
                        } else {
                            if(!method_exists($controller, $method)){
                                throw new RouteException(
                                    'Method "'.$method.'" does not exist in the '.$controller.' controller.'
                                );
                            }
                            
                            call_user_func_array([
                                new $controller,
                                $method
                            ], $args);
                        }
                        
                        return true;
                    } else { // No controller/method found. Search for callables.
                        foreach($options as $option){
                            if(is_callable($option)){
                                call_user_func_array($option, $args);
                                return true;
                            }
                        }
                    }
                    
                    throw new RouteException('Neither a "uses" method nor a callable provided.');
                } else {
                    throw new RouteException('Second argument is neither an array nor a callable.');
                }
            }
        }
        
    }
    
}

<?php

namespace Tres\router {
    
    use Exception;
    use Tres\router\Config;
    
    class HTTPRouteException extends Exception implements ExceptionInterface {}
    class RouteException extends Exception implements ExceptionInterface {}
    
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
         * The route prefixes.
         * 
         * @var array
         */
        protected static $_prefixes = [];
        
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
         * The route key for the Not Found route.
         */
        const NOT_FOUND = 'error_404';
        
        /**
         * The prefix separator.
         */
        const PREFIX_SEPARATOR = '.';
        
        /**
         * Sets the config.
         * 
         * @param array $config
         */
        public static function setConfig(array $config){
            self::$_config = $config;
        }
        
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
         * Registers the Not Found route.
         * 
         * @param  callable|array $options The route options.
         */
        public static function notFound($options){
            self::register('GET', self::NOT_FOUND, $options);
        }
        
        /**
         * Registers a route.
         * 
         * @param  string         $request The HTTP request.
         * @param  string|int     $route   The route path.
         * @param  callable|array $options The route options.
         */
        public static function register($request, $route, $options){
            if($route !== self::NOT_FOUND && !is_string($route)){
                throw new RouteException('Route path must be a string.');
            }
            
            $backtraces = debug_backtrace();
            $prefix = '';
            
            foreach(array_reverse($backtraces) as $backtrace){
                if(isset($backtrace['function']) && $backtrace['function'] === 'group'){
                    if(is_string($backtrace['args'][0])){
                        $prefix .= $backtrace['args'][0].'.';
                    } else if(is_array($backtrace['args'][0])){
                        $prefix .= $backtrace['args'][0]['prefix'].'.';
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
                
                if(is_array($options) && isset($options['alias'])){
                    $options['alias'] = $prefix.$options['alias'];
                }
                
                switch($route){
                    case self::NOT_FOUND:
                        self::$_routes[self::NOT_FOUND] = str_replace('_', '-', self::NOT_FOUND);
                        self::$_requests[self::NOT_FOUND] = $request;
                        self::$_options[self::NOT_FOUND] = $options;
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
                        echo '<h1>Error 404 - Not Found</h1><p>The page could not be found.</p>';
                    });
                }
                
                self::_run(self::NOT_FOUND);
            }
            
            return $routeMatched;
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
                if(is_array(self::$_options[$routeKey])){
                    extract(self::$_options[$routeKey]);
                    
                    if(isset($controller, $method)){
                        $controllerName = $controller;
                        $controller = self::$_config['controllers']['namespace'].'\\'.$controllerName;
                        
                        $controllerFile  = self::$_config['controllers']['dir'].'/';
                        $controllerFile .= str_replace('\\', '/', $controllerName);
                        $controllerFile .= '.php';
                        
                        if(!class_exists($controller)){
                            throw new RouteException('Controller '.$controllerName.' is not found in '.$controllerFile.'.');
                        }
                        
                        if(!method_exists($controller, $method)){
                            throw new RouteException(
                                'Method '.$method.' does not exist in the '.$controllerName.' controller.'
                            );
                        }
                        
                        call_user_func_array([
                            new $controller($args),
                            $method
                        ], $args);
                        
                        return true;
                    } else if(isset($controller)){
                        throw new RouteException('The '.$controller.' controller requires a method.');
                    } else if(isset($method)){
                        throw new RouteException('The '.$method.' method requires a controller.');
                    } else { // No controller/method found. Search for callables.
                        call_user_func_array(self::$_options[$routeKey][0], $args);
                        
                        return true;
                    }
                } else if(is_callable(self::$_options[$routeKey])){
                    call_user_func_array(self::$_options[$routeKey], $args);
                    
                    return true;
                } else {
                    throw new RouteException('Second argument is not an array, nor a callback.');
                }
            }
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
         * Groups routes together.
         */
        public static function group(){
            $args = func_get_args();
            
            if(count($args) === 1){
                if(!is_callable($args[0])){
                    throw new RouteException('The first and only argument must be callable.');
                }
            } else if(count($args) === 2){
                if(!is_callable($args[1])){
                    throw new RouteException('The second argument must be callable.');
                }
                
                $backtraces = debug_backtrace();
                unset($backtraces[0]);
                $prefixString = '';
                
                foreach($backtraces as $backtrace){
                    if(isset($backtrace['function']) && $backtrace['function'] === 'group'){
                        if(is_string($backtrace['args'][0])){
                            $prefixString .= $backtrace['args'][0].'.';
                        } else if(is_array($backtrace['args'][0])){
                            $prefixString .= $backtrace['args'][0]['prefix'].'.';
                        }
                    }
                }
                
                switch(gettype($args[0])){
                    case 'string':
                        $prefixString .= $args[0];
                    break;
                    
                    case 'array':
                        if(isset($args[0]['prefix'])){
                            $prefixString .= $args[0]['prefix'];
                        }
                        
                        // TODO: Add filters (https://github.com/tres-framework/Tres-router/issues/8)
                    break;
                    
                    default:
                        throw new RouteException('The first argument type is not valid.');
                    break;
                }
                
                $prefixString = rtrim($prefixString, '.');
                self::_addPrefix($prefixString);
                
                call_user_func($args[1]);
            } else {
                throw new RouteException('The supplied amount of arguments is not valid.');
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
         * Adds a prefix which might be nested.
         * 
         * @param string $prefixString The prefix string containing the prefix and its sub-prefixes.
         */
        protected static function _addPrefix($prefixString){
            $prefixStack = explode(self::PREFIX_SEPARATOR, $prefixString);
            $finalKey = count($prefixStack) - 1;
            
            $reference = &self::$_prefixes;
            
            foreach($prefixStack as $key => $part){
                if($finalKey !== $key){
                    if(empty($reference[$part])){
                        $reference[$part] = [];
                    }
                    
                    $reference = &$reference[$part];
                } else {
                    $reference[$part] = [];
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

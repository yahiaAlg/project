<?php
namespace Core;

/**
 * Router Class
 * Handles URL routing and dispatching to controllers
 */
class Router {
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => []
    ];
    
    /**
     * Add a GET route
     * 
     * @param string $uri The URI to match
     * @param string $controller The controller class
     * @param string $action The controller method
     * @return Router
     */
    public function get($uri, $controller, $action) {
        $this->routes['GET'][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }
    
    /**
     * Add a POST route
     * 
     * @param string $uri The URI to match
     * @param string $controller The controller class
     * @param string $action The controller method
     * @return Router
     */
    public function post($uri, $controller, $action) {
        $this->routes['POST'][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }
    
    /**
     * Add a PUT route
     * 
     * @param string $uri The URI to match
     * @param string $controller The controller class
     * @param string $action The controller method
     * @return Router
     */
    public function put($uri, $controller, $action) {
        $this->routes['PUT'][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }
    
    /**
     * Add a DELETE route
     * 
     * @param string $uri The URI to match
     * @param string $controller The controller class
     * @param string $action The controller method
     * @return Router
     */
    public function delete($uri, $controller, $action) {
        $this->routes['DELETE'][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
        return $this;
    }
    
    /**
     * Match a route
     * 
     * @param string $method The HTTP method
     * @param string $uri The URI to match
     * @return array|null The matched route or null
     */
    protected function matchRoute($method, $uri) {
        // Check for direct match
        if (isset($this->routes[$method][$uri])) {
            return [
                'info' => $this->routes[$method][$uri],
                'params' => []
            ];
        }
        
        // Check for parameterized routes
        foreach ($this->routes[$method] as $route => $info) {
            // Convert route parameters to regex pattern
            if (strpos($route, ':') !== false) {
                $pattern = preg_replace('#:([a-zA-Z0-9_]+)#', '([^/]+)', $route);
                $pattern = "#^{$pattern}$#";
                
                if (preg_match($pattern, $uri, $matches)) {
                    // Extract parameter names
                    preg_match_all('#:([a-zA-Z0-9_]+)#', $route, $paramNames);
                    array_shift($matches); // Remove full match
                    
                    // Create params array
                    $params = [];
                    foreach ($paramNames[1] as $index => $name) {
                        $params[$name] = $matches[$index];
                    }
                    
                    return [
                        'info' => $info,
                        'params' => $params
                    ];
                }
            }
        }
        
        return null;
    }
    
    /**
     * Dispatch the request to the appropriate controller
     * 
     * @param string $method The HTTP method
     * @param string $uri The request URI
     * @return void
     */
    public function dispatch($method, $uri) {
        // Remove query string
        $uri = strtok($uri, '?');
        
        // Remove trailing slash
        $uri = rtrim($uri, '/');
        
        // Add leading slash if not present
        if (empty($uri)) {
            $uri = '/';
        } elseif ($uri[0] !== '/') {
            $uri = '/' . $uri;
        }
        
        // Match route
        $match = $this->matchRoute($method, $uri);
        
        if ($match) {
            $controllerName = $match['info']['controller'];
            $action = $match['info']['action'];
            $params = $match['params'];
            
            // Create full controller class name
            $controllerClass = "App\\Controllers\\{$controllerName}";
            
            // Check if controller exists
            if (!class_exists($controllerClass)) {
                $this->notFound("Controller {$controllerClass} not found");
            }
            
            // Create controller instance
            $controller = new $controllerClass();
            
            // Check if action method exists
            if (!method_exists($controller, $action)) {
                $this->notFound("Action {$action} not found in controller {$controllerClass}");
            }
            
            // Add route parameters to request
            $_REQUEST['route_params'] = $params;
            
            // Call controller action with parameters
            call_user_func_array([$controller, $action], $params);
        } else {
            $this->notFound("No route found for {$method} {$uri}");
        }
    }
    
    /**
     * Handle 404 Not Found
     * 
     * @param string $message Error message
     * @return void
     */
    protected function notFound($message = 'Page not found') {
        http_response_code(404);
        
        $view = new View();
        $view->render('errors/404', ['message' => $message]);
        exit;
    }
    

    /**
     * Load routes from file
     * 
     * @param string $file Path to routes file
     * @return Router
     */
    public function loadRoutes($file) {
        if (file_exists($file)) {
            $router = $this; // Use $this as $router
            require_once $file;
        }
        return $this;
    }
}
<?php
/**
 * URL Router Class
 * Native PHP MVC Pattern
 */

class Router {
    private $routes = [];
    private $params = [];
    
    /**
     * Add Route
     */
    public function add($method, $uri, $controller, $action = 'index') {
        $this->routes[strtoupper($method)][$uri] = [
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    /**
     * Add GET Route
     */
    public function get($uri, $controller, $action = 'index') {
        $this->add('GET', $uri, $controller, $action);
    }
    
    /**
     * Add POST Route
     */
    public function post($uri, $controller, $action = 'index') {
        $this->add('POST', $uri, $controller, $action);
    }
    
    /**
     * Add PUT Route
     */
    public function put($uri, $controller, $action = 'index') {
        $this->add('PUT', $uri, $controller, $action);
    }
    
    /**
     * Add DELETE Route
     */
    public function delete($uri, $controller, $action = 'index') {
        $this->add('DELETE', $uri, $controller, $action);
    }
    
    /**
     * Dispatch Route
     */
    public function dispatch($uri) {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->removeQueryString($uri);
        
        // Check for exact match
        if (isset($this->routes[$method][$uri])) {
            return $this->executeRoute($this->routes[$method][$uri]);
        }
        
        // Check for parameterized routes
        foreach ($this->routes[$method] as $route => $params) {
            if ($this->matchRoute($route, $uri)) {
                return $this->executeRoute($params);
            }
        }
        
        // No route found
        http_response_code(404);
        echo "404 - Page Not Found";
        return false;
    }
    
    /**
     * Execute Route
     */
    private function executeRoute($params) {
        $controllerName = $params['controller'];
        $actionName = $params['action'];
        
        // Include controller file
        $controllerFile = APP_PATH . '/controllers/' . $controllerName . 'Controller.php';
        
        if (!file_exists($controllerFile)) {
            throw new Exception("Controller file not found: {$controllerFile}");
        }
        
        require_once $controllerFile;
        
        $controllerClassName = $controllerName . 'Controller';
        
        if (!class_exists($controllerClassName)) {
            throw new Exception("Controller class not found: {$controllerClassName}");
        }
        
        $controller = new $controllerClassName();
        
        if (!method_exists($controller, $actionName)) {
            throw new Exception("Method not found: {$actionName} in {$controllerClassName}");
        }
        
        // Extract route parameters
        $routeParams = $this->extractRouteParams();
        
        // Call controller method with parameters
        return call_user_func_array([$controller, $actionName], $routeParams);
    }
    
    /**
     * Match Route Pattern
     */
    private function matchRoute($route, $uri) {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            // Store parameters (excluding full match)
            $this->params = array_slice($matches, 1);
            return true;
        }
        
        return false;
    }
    
    /**
     * Extract Route Parameters
     */
    private function extractRouteParams() {
        return $this->params;
    }
    
    /**
     * Remove Query String from URI
     */
    private function removeQueryString($uri) {
        $parts = explode('?', $uri);
        return $parts[0];
    }
    
    /**
     * Get Current Route
     */
    public function getCurrentRoute() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->removeQueryString($_SERVER['REQUEST_URI']);
        
        foreach ($this->routes[$method] as $route => $params) {
            if ($this->matchRoute($route, $uri)) {
                return [
                    'method' => $method,
                    'uri' => $uri,
                    'route' => $route,
                    'params' => $params,
                    'routeParams' => $this->params
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Generate URL
     */
    public function url($name, $params = []) {
        // Find route by name (you can extend this to support named routes)
        foreach ($this->routes['GET'] as $route => $routeParams) {
            if ($routeParams['controller'] === $name) {
                $url = $route;
                
                // Replace parameters
                foreach ($params as $key => $value) {
                    $url = str_replace('{' . $key . '}', $value, $url);
                }
                
                return BASE_URL . $url;
            }
        }
        
        return BASE_URL;
    }
}
?>

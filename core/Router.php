<?php

class Router
{
    private $routes = [];

    public function add(string $route, string $controller, string $action)
    {
        $this->routes[$route] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function dispatch(string $url)
    {
        // exact-match only, no pattern routes
        if (array_key_exists($url, $this->routes)) {
            $controllerName = $this->routes[$url]['controller'];
            $action = $this->routes[$url]['action'];

            $controllerFile = APP_ROOT . '/app/Controllers/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                
                $controller = new $controllerName();
                if (is_callable([$controller, $action])) {
                    $controller->$action();
                } else {
                    $this->notFound("Method $action not found in controller $controllerName");
                }
            } else {
                $this->notFound("Controller class $controllerName not found");
            }
        } else {
            $this->notFound("No route matched for $url");
        }
    }
    
    private function notFound($msg = '')
    {
        http_response_code(404);
        echo "404 Not Found - " . (function_exists('e') ? e($msg) : htmlspecialchars($msg));
        exit;
    }
}

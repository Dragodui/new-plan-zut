<?php
namespace App;

class Router
{
    private $routes = [];

    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *'); 
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        // echo json_encode($this->routes);
        // exit;

        foreach ($this->routes[$method] as $path => $handler) {
            if ($path === $uri) {
                [$class, $method] = $handler;
                // $instance = new $class();
                // echo get_class($instance);
                if (class_exists($class) && method_exists($class, $method)) {
                    (new $class())->$method();
                } else {
                    $this->sendError(500, 'Handler not found');
                }
                return;
            }
        }

        $this->sendError(404, 'Route not found');
    }

    private function sendError($status, $message)
    {
        http_response_code($status);
        echo json_encode(['error' => $message]);
        exit;
    }
}

<?php
require __DIR__ . '/../vendor/autoload.php';

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$dispatcher = simpleDispatcher(function(RouteCollector $r) {
    $r->get('/', ['HmtAdmissions\Api\Controllers\HomeController', 'index']);
    $r->post('/v1/auth/register', ['HmtAdmissions\Api\Controllers\AuthController', 'register']);
    $r->post('/v1/auth/login', ['HmtAdmissions\Api\Controllers\AuthController', 'login']);
    $r->get('/v1/lesson-packs/today', ['HmtAdmissions\Api\Controllers\LessonPackController', 'getToday']);
});

// Fetch method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

header('Content-Type: application/json');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        [$class, $method] = $handler;
        $controller = new $class();
        $response = $controller->$method($vars);
        echo json_encode($response);
        break;
}

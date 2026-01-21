<?php

require_once __DIR__ . '/../src/Bootstrap.php';

use App\Bootstrap;
use App\Request;
use App\Router;
use App\Response;
use App\Config;
use App\Middleware\CorsMiddleware;
use App\Middleware\RequestIdMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\AuthController;
use App\Controllers\QuizController;
use App\Services\QuizService;
use App\Exceptions\AppException;

Bootstrap::init();

$request = new Request();
$router = new Router();

// Global Middleware
$router->addMiddleware(new CorsMiddleware());
$router->addMiddleware(new RequestIdMiddleware());

// Auth Routes
$authController = new AuthController();

$router->add('POST', '/v1/auth/anonymous', function (Request $req) use ($authController) {
    return $authController->anonymous($req);
});

$router->add('POST', '/v1/auth/login', function (Request $req) use ($authController) {
    return $authController->login($req);
});

$router->add('POST', '/v1/auth/register', function (Request $req) use ($authController) {
    return $authController->register($req);
});

$router->add('GET', '/v1/auth/me', function (Request $req) {
    return (new AuthMiddleware())->handle($req, function ($r) {
        return Response::json([
            'user_id' => $r->getAttribute('user_id'),
            'email' => $r->getAttribute('email'),
            'role' => $r->getAttribute('role')
        ]);
    });
});

// Quiz Dependencies
$db = Bootstrap::getService('db') ?? new PDO(
    'mysql:host=' . Config::get('DB_HOST') . ';dbname=' . Config::get('DB_NAME') . ';charset=utf8mb4',
    Config::get('DB_USER'),
    Config::get('DB_PASS'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
$questionRepo = Bootstrap::getService('question_repo');
$quizService = new QuizService($db, $questionRepo);
$quizController = new QuizController($quizService);

// Quiz Routes
$router->add('GET', '/v1/quiz/start', function (Request $req) use ($quizController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($quizController) {
        return $quizController->start($r);
    });
});

$router->add('POST', '/v1/quiz/answer', function (Request $req) use ($quizController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($quizController) {
        return $quizController->answer($r);
    });
});

// Wrong Questions Routes
$router->add('GET', '/v1/wrong-questions', function (Request $req) use ($quizController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($quizController) {
        return $quizController->getWrongQuestions($r);
    });
});

$router->add('GET', '/v1/wrong-questions/quiz', function (Request $req) use ($quizController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($quizController) {
        return $quizController->wrongQuestionsQuiz($r);
    });
});

// Questions List Route (题库浏览)
$router->add('GET', '/v1/questions', function (Request $req) use ($questionRepo) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($questionRepo) {
        $params = $r->getQueryParams();
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 20);
        $examPath = $params['exam_path'] ?? null;
        $subject = $params['subject'] ?? null;
        
        $result = $questionRepo->findAll($examPath, $subject, $page, $limit);
        return Response::json($result);
    });
});

// Dispatch
try {
    $router->dispatch($request);
} catch (AppException $e) {
    $body = [
        "error" => [
            "code" => $e->getErrorCode(),
            "message" => $e->getMessage(),
            "details" => $e->getDetails()
        ],
        "request_id" => $request->getAttribute('request_id')
    ];
    $response = Response::json($body, $e->getHttpStatus());
    $response->setHeader('Access-Control-Allow-Origin', '*');
    $response->send();
} catch (\Throwable $e) {
    $debug = Config::get('APP_DEBUG', 'false') === 'true';

    $body = [
        "error" => [
            "code" => "INTERNAL_ERROR",
            "message" => "Server Error",
            "details" => $debug ? [
                'type' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ] : []
        ],
        "request_id" => $request->getAttribute('request_id')
    ];

    // In production, we might want to log the actual error here

    $response = Response::json($body, 500);
    $response->setHeader('Access-Control-Allow-Origin', '*');
    $response->send();
}

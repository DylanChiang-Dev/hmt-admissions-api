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
use App\Controllers\ProgressController;
use App\Controllers\ReviewController;
use App\Repositories\MemoryProgressRepository;
use App\Repositories\MemoryReviewRepository;
use App\Services\ProgressService;
use App\Services\ReviewService;
use App\Services\AttemptService;
use App\Services\LessonPackService;
use App\Controllers\AttemptsController;
use App\Controllers\LessonPackController;
use App\Repositories\Memory\MemoryAttemptRepository;
use App\Exceptions\AppException;
use App\Exceptions\NotFoundException;
use Throwable;

Bootstrap::init();

$request = new Request();
$router = new Router();

// Global Middleware
$router->addMiddleware(new CorsMiddleware());
$router->addMiddleware(new RequestIdMiddleware());

// Routes
$authController = new AuthController();

$router->add('POST', '/v1/auth/anonymous', function (Request $req) use ($authController) {
    return $authController->anonymous($req);
});

$router->add('POST', '/v1/auth/login', function (Request $req) use ($authController) {
    return $authController->login($req);
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

// Progress & Review Dependencies
$progressRepo = Bootstrap::getService('progress_repo');
$progressService = new ProgressService($progressRepo);
$progressController = new ProgressController($progressService);

$reviewRepo = Bootstrap::getService('review_repo');
$reviewService = new ReviewService($reviewRepo);
$reviewController = new ReviewController($reviewService);

$attemptRepo = Bootstrap::getService('attempt_repo');
$attemptService = new AttemptService($attemptRepo);
$attemptsController = new AttemptsController($attemptService);

// Lesson Pack Dependencies
$lessonPackRepo = Bootstrap::getService('lesson_pack_repo');
$lessonPackService = new LessonPackService($lessonPackRepo);
$lessonPackController = new LessonPackController($lessonPackService);

// Progress Routes
$router->add('GET', '/v1/progress', function (Request $req) use ($progressController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($progressController) {
        return $progressController->get($r);
    });
});

// Review Routes
$router->add('GET', '/v1/review/queue', function (Request $req) use ($reviewController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($reviewController) {
        return $reviewController->getQueue($r);
    });
});

$router->add('POST', '/v1/review/complete', function (Request $req) use ($reviewController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($reviewController) {
        return $reviewController->complete($r);
    });
});

// Attempt Routes
$router->add('POST', '/v1/attempts', function (Request $req) use ($attemptsController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($attemptsController) {
        return $attemptsController->submit($r);
    });
});

// Lesson Pack Routes
$router->add('GET', '/v1/lesson-packs/today', function (Request $req) use ($lessonPackController) {
    return (new AuthMiddleware())->handle($req, function ($r) use ($lessonPackController) {
        return $lessonPackController->getToday($r);
    });
});

$router->add('GET', '/test', function (Request $req) {
    return Response::json([
        'status' => 'ok',
        'message' => 'System operational',
        'request_id' => $req->getAttribute('request_id')
    ]);
});

$router->add('GET', '/error-test', function (Request $req) {
    throw new NotFoundException("Testing", "ERR_TEST");
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
    Response::json($body, $e->getHttpStatus());
} catch (Throwable $e) {
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

    Response::json($body, 500);
}

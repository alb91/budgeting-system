<?php 

require __DIR__ . '/../vendor/autoload.php'; 
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Message\ServerRequestInterface as Request; 
use Psr\Http\Server\RequestHandlerInterface as RequestHandler; 
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;  

$dotenv = Dotenv::createImmutable(__DIR__ . '/../'); 
$dotenv->load(); 

$app = AppFactory::create();  

$app->addRoutingMiddleware();

$displayErrorDetails = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);

$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);

$app->add(function (Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request); 

    return $response
        ->withHeader('Access-Control-Allow-Origin', $_ENV['FRONTEND_URL'])
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS'); 
});

$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response
        ->withHeader('Access-Control-Allow-Origin', $_ENV['FRONTEND_URL'])
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withStatus(200);
});

(require __DIR__ . '/../src/Routes/routes.php')($app);

$app->run(); 
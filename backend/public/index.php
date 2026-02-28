<?php 

require __DIR__ . '/../vendor/autoload.php'; 
use Psr\Http\Message\ResponseInterface as Response; 
use Psr\Http\Message\ServerRequestInterface as Request; 
use Psr\Http\Server\RequestHandlerInterface as RequestHandler; 
use Slim\Factory\AppFactory;
use Dotenv\Dotenv; 
use App\Database; 

$dotenv = Dotenv::createImmutable(__DIR__ . '/../'); 
$dotenv->load(); 

$app = AppFactory::create();  

$app->addRoutingMiddleware();

$app->add(function (Request $request, RequestHandler $handler): Response {
    $response = $handler->handle($request); 

    return $response
    // por ahora voy a hardcodear pero tengo que ver cómo crear una variable para el localhost
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
//puedo agregar esto directamente en un middleware?? que iría adjunto a la ruta?? 
// ahora mismo esto sigue sin funcionar, primero voy a ver cómo arreglar esto para saber bien qué información debo de tener en la sección de index, 
// también necesio quitar ese localhost directo, 
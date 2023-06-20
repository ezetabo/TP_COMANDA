<?php
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Cookies;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/Logger.php';
require_once './middlewares/AutentificadorJWT.php';
require_once './middlewares/Check.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';



// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
define('PASS_SECRET', $_ENV['CLAVE_SECRETA']);
define('ENCRYPT_TYPE', $_ENV['TIPO_ENCRIPTACION']);
// Instantiate App
$app = AppFactory::create();

// Add error middleware
$errorMiddleware = function ($request, $exception, $displayErrorDetails) use ($app) {
  $statusCode = 500;
  $errorMessage = $exception->getMessage();
  $response = $app->getResponseFactory()->createResponse($statusCode);
  $response->getBody()->write(json_encode(['error' => $errorMessage]));

  return $response->withHeader('Content-Type', 'application/json');
};
$app->addErrorMiddleware(true, true, true)
  ->setDefaultErrorHandler($errorMiddleware);

$app->addBodyParsingMiddleware();

// Routes

$app->post('/login', \UsuarioController::class . '::Login')
  ->add(\Logger::class . '::ValidarEstado')
  ->add(\Logger::class . '::ValidarMailYpass')
  ->add(\Logger::class . '::LimpiarCookie');

$app->get('/logout', function (Request $request, Response $response) {
  $payload = json_encode(array("mensaje" => 'Sesion Cerrada'));
  $response->getBody()->write($payload);
  return $response->withHeader('Content-Type', 'application/json');
})->add(\Logger::class . '::LimpiarCookie');

$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':TraerTodos');
  $group->get('/{id}', \UsuarioController::class . ':TraerUno');
  $group->post('[/]', \UsuarioController::class . '::CargarUno')
    ->add(\Check::class . '::ExisteMail')
    ->add(\Check::class . '::Rol')
    ->add(\Check::class . '::Clave')
    ->add(\Check::class . '::Mail');
  $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');
  $group->put('/modificar', \UsuarioController::class . ':ModificarUno');
})
  ->add(\Logger::class . '::ValidarRol')
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');


$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoController::class . ':TraerUno');
  $group->post('[/]', \ProductoController::class . '::CargarUno')
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'socio');
    })
    ->add(\Logger::class . '::ValidarUsuario')
    ->add(\Logger::class . '::EstaLogueado');
  $group->delete('/{id}', \ProductoController::class . ':BorrarUno')
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'socio');
    })
    ->add(\Logger::class . '::ValidarUsuario')
    ->add(\Logger::class . '::EstaLogueado');
  $group->post('/editar', \ProductoController::class . ':ModificarUno')
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'socio');
    })
    ->add(\Logger::class . '::ValidarUsuario')
    ->add(\Logger::class . '::EstaLogueado');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->get('/{id}', \MesaController::class . ':TraerUno');
  $group->post('[/]', \MesaController::class . '::CargarUno');
  $group->delete('/{id}', \MesaController::class . ':BorrarUno');
  $group->post('/editar', \MesaController::class . ':ModificarUno');
})
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{id}', \PedidoController::class . ':TraerUno');
  $group->post('[/]', \PedidoController::class . '::CargarUno');
  $group->delete('/{id}', \PedidoController::class . ':BorrarUno');
  $group->post('/editar', \PedidoController::class . ':ModificarUno');
  $group->put('/modificar', \PedidoController::class . ':Pruebas');
})
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');


// JWT test routes
$app->group('/jwt', function (RouteCollectorProxy $group) {

  $group->post('/crearToken', function (Request $request, Response $response) {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $perfil = $parametros['perfil'];
    $alias = $parametros['alias'];

    $datos = array('usuario' => $usuario, 'perfil' => $perfil, 'alias' => $alias);

    $token = AutentificadorJWT::CrearToken($datos);
    $payload = json_encode(array('jwt' => $token));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

  $group->get('/devolverPayLoad', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
      $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

  $group->get('/devolverDatos', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
      $payload = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

  $group->get('/verificarToken', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    $esValido = false;

    try {
      AutentificadorJWT::verificarToken($token);
      $esValido = true;
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    if ($esValido) {
      $payload = json_encode(array('valid' => $esValido));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });
});

$app->get('[/]', function (Request $request, Response $response) {
  $payload = json_encode(array("mensaje" => 'TP_TABOADA_EZEQUIEL'));
  $response->getBody()->write($payload);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->run();

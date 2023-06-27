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
require_once './middlewares/Check.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/AtenderController.php';



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
/* ----------------------------------------------------------------- */
/* -------------------   Logueo  ----------------------------------- */
/* ----------------------------------------------------------------- */
$app->post('/login', \UsuarioController::class . '::Login')
  ->add(\Logger::class . '::ValidarEstado')
  ->add(\Logger::class . '::ValidarMailYpass')
  ->add(\Logger::class . '::LimpiarCookie');

$app->get('/logout', function (Request $request, Response $response) {
  $payload = json_encode(array("mensaje" => 'Sesion Cerrada'));
  $response->getBody()->write($payload);
  $cookies = $_COOKIE;
  foreach ($cookies as $nombre => $valor) {
    setcookie($nombre, '', time() - 3600);
  }
  return $response->withHeader('Content-Type', 'application/json');
});


/* ----------------------------------------------------------------- */
/* -------------------  Usuarios ----------------------------------- */
/* ----------------------------------------------------------------- */
$app->group('/usuarios', function (RouteCollectorProxy $group) {
  $group->get('[/]', \UsuarioController::class . ':TraerTodos');
  $group->get('/{id}', \UsuarioController::class . ':TraerUno');
  $group->post('[/]', \UsuarioController::class . '::CargarUno')
    ->add(\Check::class . '::MailDisponible')
    ->add(\Check::class . '::Rol')
    ->add(\Check::class . '::Clave')
    ->add(\Check::class . '::Mail');
  $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');
  $group->put('/modificar', \UsuarioController::class . ':ModificarUno');
})
  ->add(\Logger::class . '::ValidarRol')
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');



/* ----------------------------------------------------------------- */
/* ------------------- Prdocutos ----------------------------------- */
/* ----------------------------------------------------------------- */
$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class . ':TraerTodos');
  $group->get('/{id}', \ProductoController::class . ':TraerUno');
  $group->post('[/]', \ProductoController::class . '::CargarUno')->add(\Logger::class . '::ValidarRol');
  $group->delete('/{id}', \ProductoController::class . ':BorrarUno')->add(\Logger::class . '::ValidarRol');
  $group->post('/editar', \ProductoController::class . ':ModificarUno')->add(\Logger::class . '::ValidarRol');
})
  ->add(\Logger::class . '::ValidarRol')
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');


/* ----------------------------------------------------------------- */
/* -------------------   Mesas   ----------------------------------- */
/* ----------------------------------------------------------------- */
$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class . ':TraerTodos');
  $group->get('/{id}', \MesaController::class . ':TraerUno');
  $group->post('[/]', \MesaController::class . '::CargarUno');
  $group->delete('/{id}', \MesaController::class . ':BorrarUno');
  $group->post('/editar', \MesaController::class . ':ModificarUno');
})
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');


/* ----------------------------------------------------------------- */
/* -------------------   Pedidos ----------------------------------- */
/* ----------------------------------------------------------------- */
$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class . ':TraerTodos');
  $group->get('/{id}', \PedidoController::class . ':TraerUno');
  $group->post('[/]', \PedidoController::class . '::CargarUno')
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'mozo');
    });
  $group->delete('/{id}', \PedidoController::class . ':BorrarUno');
  $group->post('/editar', \PedidoController::class . ':ModificarUno');
  $group->put('/modificar', \PedidoController::class . ':Pruebas');
})
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');


/* ----------------------------------------------------------------- */
/* -------------------   Atender ----------------------------------- */
/* ----------------------------------------------------------------- */
$app->group('/atender', function (RouteCollectorProxy $group) {
  $group->post('[/]', \AtenderController::class . '::Atender')
    ->add(\Check::class . '::ExisteMesa')
    ->add(function ($request, $handler) {
      return \Check::CampoRequerido($request, $handler, 'id_mesa');
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequerido($request, $handler, 'cliente');
    })
    ->add(function ($request, $handler) {
      return \Check::CampoOpcionalFile($request, $handler, 'foto');
    })
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'mozo');
    });

  $group->post('/ordenar', \AtenderController::class . '::GenerarOrden')
    ->add(function ($request, $handler) {
      $parametros = $request->getParsedBody();
      return \Check::ExisteProducto($request, $handler, $parametros['producto']);
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequerido($request, $handler, 'producto');
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequerido($request, $handler, 'codigo_pedido');
    })
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'mozo');
    });

  $group->get('/preparar', \AtenderController::class . '::PrepararOrden')
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoParam($request, $handler, 'id');
    })
    ->add(\Check::class . '::OrdenDisponible');
})
  ->add(\Logger::class . '::EstaLogueado');

/* ----------------------------------------------------------------- */
/* -------------------   Ordenes ----------------------------------- */
/* ----------------------------------------------------------------- */
$app->group('/ordenes', function (RouteCollectorProxy $group) {
  $group->get('[/]', \AtenderController::class . '::Todas')
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'mozo');
    });

  $group->get('/pendientes', \AtenderController::class . '::Pendientes');
})
  ->add(\Logger::class . '::EstaLogueado');


$app->get('/consultar', \AtenderController::class . '::ConsultaCliente')
  ->add(function ($request, $handler) {
    return \Check::CampoRequeridoParam($request, $handler, 'codigo_mesa');
  })
  ->add(function ($request, $handler) {
    return \Check::CampoRequeridoParam($request, $handler, 'codigo_pedido');
  });

$app->get('[/]', function (Request $request, Response $response) {
  $payload = json_encode(array("mensaje" => 'TP_TABOADA_EZEQUIEL'));
  $response->getBody()->write($payload);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->run();

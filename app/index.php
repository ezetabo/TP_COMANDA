<?php
error_reporting(-1);
ini_set('display_errors', 1);
date_default_timezone_set('America/Argentina/Buenos_Aires');


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
require_once './controllers/EstadisticasController.php';



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
  $response->getBody()->write(json_encode(['AVISO' => $errorMessage]));

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
  ->add(\Logger::class . '::LimpiarCookie')
  ->add(\Logger::class . '::LogueoUnico');

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

  $group->get('/servir', \AtenderController::class . '::ServirOrden')
    ->add(\Check::class . '::OrdenListaParaServir')
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoParam($request, $handler, 'id');
    })
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'mozo');
    });

  $group->get('/cobrar', \AtenderController::class . '::Cobrar')
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'mozo');
    });

  $group->get('/cerrar', \AtenderController::class . '::Cerrar')
    ->add(\Check::class . '::ExisteMesaPorCodigo')
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoParam($request, $handler, 'codigo_mesa');
    })
    ->add(\Logger::class . '::ValidarRol');
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

  $group->get('/para_servir', \AtenderController::class . '::ParaServir')
    ->add(function ($request, $handler) {
      return \Logger::ValidarRol($request, $handler, 'mozo');
    });;
})
  ->add(\Logger::class . '::EstaLogueado');


/* ----------------------------------------------------------------- */
/* -------------------   Cliente ----------------------------------- */
/* ----------------------------------------------------------------- */
$app->group('/cliente', function (RouteCollectorProxy $group) {

  $group->get('[/]', \AtenderController::class . '::ConsultaCliente')
    ->add(\Check::class . '::MesaParaConsultas')
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoParam($request, $handler, 'codigo_mesa');
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoParam($request, $handler, 'codigo_pedido');
    });

  $group->post('/encuesta', \AtenderController::class . '::CrearEncuesta')
    ->add(function ($request, $handler) {
      return \Check::CampoRequerido($request, $handler, 'comentario');
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoNumericoLimite($request, $handler, 'pts_mesa', 10);
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoNumericoLimite($request, $handler, 'pts_restaurante', 10);
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoNumericoLimite($request, $handler, 'pts_mozo', 10);
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequeridoNumericoLimite($request, $handler, 'pts_cocinero', 10);
    })
    ->add(\Check::class . '::EncuestaDisponible')
    ->add(function ($request, $handler) {
      return \Check::CampoRequerido($request, $handler, 'codigo_pedido');
    })
    ->add(function ($request, $handler) {
      return \Check::CampoRequerido($request, $handler, 'codigo_mesa');
    });
});

/* ----------------------------------------------------------------- */
/* ------------------- Estadisticas -------------------------------- */
/* ----------------------------------------------------------------- */
$app->group('/estadisticas', function (RouteCollectorProxy $group) {

  $group->get('/usuarios', \EstadisticasController::class . ':TraerUsuarios');

  $group->get('/pedidos[/{limite}]', \EstadisticasController::class . ':TraerPedidos')
    ->setArgument('limite', 3);

  $group->get('/mesas[/{limite}]', \EstadisticasController::class . ':TraerMesas')
    ->setArgument('limite', 1)
    ->add(\Check::class . '::ValidarEntreFechasParam');

  $group->get('/comentarios[/{limite}]', \EstadisticasController::class . ':TraerComentarios')
    ->setArgument('limite', 3);
})
  ->add(function ($request, $handler) {
    return \Check::CampoRequeridoParam($request, $handler, 'tipo');
  })
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');

$app->post('/carga_csv', \ProductoController::class . ':CargaCsv')
  ->add(function ($request, $handler) {
    return \Check::CampoOpcionalFile($request, $handler, 'csv');
  })
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');

$app->get('/descarga_csv', \ProductoController::class . ':GenerarCsvOnline')
  ->add(function ($request, $handler) {
    return \Check::CampoOpcionalFile($request, $handler, 'csv');
  })
  ->add(\Logger::class . '::ValidarUsuario')
  ->add(\Logger::class . '::EstaLogueado');


$app->get('[/]', function (Request $request, Response $response) {
  $payload = json_encode(array("mensaje" => 'TP_TABOADA_EZEQUIEL'));
  $response->getBody()->write($payload);
  return $response->withHeader('Content-Type', 'application/json');
});

$app->run();

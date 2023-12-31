<?php
require_once './interfaces/IApiUsable.php';
require_once './helpers/helpers.php';

class UsuarioController extends Usuario implements IApiUsable
{
  public static function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    $usr = newUsuario($parametros);
    $usr->crearUsuario();
    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    // Buscamos usuario por nombre
    $usr = $args['id'];
    $usuario = Usuario::obtenerUsuario($usr);
    $payload = json_encode($usuario);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Usuario::obtenerTodos();
    $payload = json_encode(array("lista_usuario" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function ModificarUno($request, $response, $args)
  {    
    $usr = editarUsuario($args['id'],$request->getParsedBody());  
    Usuario::modificarUsuario($usr);

    $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $usuarioId = $args['id'];
    Usuario::borrarUsuario($usuarioId);

    $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }

  public static function Login($request, $response, $args)
  {
    $dts = $request->getParsedBody();
    $token = AutentificadorJWT::CrearToken(array('id'=>$dts['id'],'mail'=>$dts['mail'],'clave'=>$dts['clave'],'rol'=>$dts['rol'],'estado'=>$dts['estado']));
    Logs::crearLogs(newLogs(array('usuario'=>$dts['mail'],'rol'=>$dts['rol'])));
    setcookie('JWT', $token, time()+3600*8, '/', 'localhost', false, true);
    $payload = json_encode(array("mensaje" => "Login exitoso",'rol'=>$dts['rol']));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
  }
}

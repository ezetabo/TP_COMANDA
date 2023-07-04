<?php
require_once './middlewares/AutentificadorJWT.php';
class Logger
{
    public static function ValidarMailYpass($request, $handler)
    {
        $parsedBody = $request->getParsedBody();
        $mail = $parsedBody['mail'];
        $clave = $parsedBody['clave'];
        $usr = Usuario::BuscarPormail($mail);
        if ($usr !== false && password_verify($clave, $usr->clave)) {
            $parsedBody['rol'] = $usr->rol;
            $parsedBody['estado'] = $usr->estado;
            $parsedBody['id'] = $usr->id;
            $request = $request->withParsedBody($parsedBody);
            return $handler->handle($request);
        }
        throw new Exception("Mail y/o password no válidos");
    }

    public static function LogueoUnico($request, $handler)
    {
        $parsedBody = $request->getParsedBody();
        $cookies = $request->getCookieParams();
        $mail = $parsedBody['mail'];
        $clave = $parsedBody['clave'];
        if (isset($cookies['JWT'])) {
            $datos = AutentificadorJWT::ObtenerData($cookies['JWT']);
            if ($datos->mail == $mail && $datos->clave == $clave) {
                throw new Exception("Ya tiene una sesion abierta");
            }
        }
        return $handler->handle($request);
    }

    public static function ValidarEstado($request, $handler)
    {
        $parsedBody = $request->getParsedBody();
        $estado = $parsedBody['estado'];
        if ($estado == 'activo') {
            return $handler->handle($request);
        }
        throw new Exception("Los datos no coresponden a un usuario activo");
    }

    public static function EstaLogueado($request, $handler)
    {
        $cookies = $request->getCookieParams();
        if (isset($cookies['JWT'])) {
            if (AutentificadorJWT::ObtenerData($cookies['JWT'])->estado == 'activo') {
                return $handler->handle($request);
            }
            throw new Exception("Los datos no coresponden a un usuario activo");
        }
        throw new Exception('Debe estar logueado para acceder a esta ruta.');
    }

    public static function ValidarUsuario($request, $handler)
    {
        try {
            $cookies = $request->getCookieParams();
            $token = $cookies['JWT'];
            AutentificadorJWT::VerificarToken($token);
            return $handler->handle($request);
        } catch (Exception $th) {
            throw new Exception($th->getMessage());
        }
    }

    public static function ValidarRol($request, $handler, $rol = false)
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['JWT'];
        $datos = AutentificadorJWT::ObtenerData($token);
        if ((!$rol && $datos->rol == 'socio') || $rol && ($datos->rol == $rol || $datos->rol == 'socio')) {
            return $handler->handle($request);
        }
        throw new Exception("Ud no cuenta con los permisos para esta ruta");
    }

    public static function LimpiarCookie($request, $handler)
    {
        setcookie("JWT", "", time() - 3600);
        return $handler->handle($request);
    }
}

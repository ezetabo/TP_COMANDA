<?php
require_once './models/Usuario.php';
class Check
{

    public static function MailDisponible($request, $handler)
    {
        $parsedBody = $request->getParsedBody();
        $email = $parsedBody['mail'];
        $usr = Usuario::BuscarPorMail($email);
        if ($usr === false) {
            return $handler->handle($request);
        }
        throw new Exception("El mail ya existe en la BD");
    }

    public static function ExisteId($request, $handler, $id)
    {
        $usr = Usuario::obtenerUsuario($id);
        if ($usr !== false) {
            if ($usr->estado == 'baja') {
                throw new Exception("El id ya fue dado de baja");
            }
            return $handler->handle($request);
        }
        throw new Exception("El id no existe en la BD");
    }

    public static function ExisteMesa($request, $handler)
    {
        $args = $request->getParsedBody();
        $usr = Mesa::obtenerMesa($args['id_mesa']);
        if ($usr !== false) {
            return $handler->handle($request);
        }
        throw new Exception("El id no existe en la BD");
    }

    public static function ExisteProducto($request, $handler, $producto)
    {
        $usr = Producto::obtenerProductoPorNombre($producto);
        if ($usr !== false) {
            return $handler->handle($request);
        }
        throw new Exception('El producto [ ' . $producto . ' ] no existe en la BD');
    }

    public static function Mail($request, $handler)
    {
        $parsedBody = $request->getParsedBody();
        if (isset($parsedBody['mail']) && !empty($parsedBody['mail'])) {
            return $handler->handle($request);
        }
        throw new Exception("El campo mail es requerido y no puede estar vacio");
    }

    public static function Clave($request, $handler)
    {
        $parsedBody = $request->getParsedBody();
        if (isset($parsedBody['clave']) && !empty($parsedBody['clave'])) {
            return $handler->handle($request);
        }
        throw new Exception("El campo clave es requerido y no puede estar vacio");
    }
    public static function Rol($request, $handler)
    {
        $parsedBody = $request->getParsedBody();
        if (isset($parsedBody['rol']) && !empty($parsedBody['rol'])) {
            return $handler->handle($request);
        }
        throw new Exception("El campo rol es requerido y no puede estar vacio");
    }

    public static function CampoOpcional($request, $handler, $campo)
    {
        $parsedBody = $request->getParsedBody();
        if (isset($parsedBody[$campo]) && empty($parsedBody[$campo])) {
            throw new Exception('Si agrego el campo ' . $campo . ' no puede estar vacio');
        }
        return $handler->handle($request);
    }

    public static function CampoRequerido($request, $handler, $campo)
    {
        $parsedBody = $request->getParsedBody();
        if (isset($parsedBody[$campo]) && !empty($parsedBody[$campo])) {
            return $handler->handle($request);
        }
        throw new Exception('El campo ' . $campo . ' es requerido y no puede estar vacio');
    }

    public static function CampoOpcionalFile($request,  $handler, $campo)
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (isset($uploadedFiles[$campo]) && $uploadedFiles[$campo]->getError() == UPLOAD_ERR_NO_FILE) {
            throw new Exception('Si agrego el campo ' . $campo . ' no puede estar vacio');
        }
        return $handler->handle($request);
    }

    public static function CampoRequeridoFile($request,  $handler, $campo)
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (isset($uploadedFiles[$campo]) && !$uploadedFiles[$campo]->getError() == UPLOAD_ERR_NO_FILE) {
            return $handler->handle($request);
        }

        throw new Exception('El campo ' . $campo . ' es requerido y no puede estar vacio');
    }

    public static function CampoRequeridoNumerico($request, $handler, $campo)
    {
        $parsedBody = $request->getParsedBody();
        if (isset($parsedBody[$campo]) && !empty($parsedBody[$campo]) && $parsedBody[$campo] > 0) {
            return $handler->handle($request);
        }
        throw new Exception('El campo ' . $campo . ' es requerido, debe ser numérico y mayor a 0');
    }

    public static function CampoRequeridoParam($request, $handler, $campo)
    {
        $args = $request->getQueryParams();

        if (isset($args[$campo]) && !empty($args[$campo])) {
            return $handler->handle($request);
        }
        throw new Exception('El campo ' . $campo . ' es requerido y no puede estar vacio');
    }

    public static function OrdenDisponible($request, $handler)
    {
        $parametros = $request->getQueryParams();
        $cookies = $request->getCookieParams();
        $token = $cookies['JWT'];
        $datos = AutentificadorJWT::ObtenerData($token);
        $orden = Orden::obtenerOrden($parametros['id']);
        $producto = Producto::obtenerProductoPorNombre($orden->producto);
        if ($orden !== false) {           
            if ($producto->sector == $datos->rol) {
                if ($orden->estado != 'listo para servir' && $orden->estado != 'entregado') {
                    return $handler->handle($request);
                }
                throw new Exception('El estado de la orden N° ' . $orden->id . ' es [ ' . $orden->estado . ' ]');
            }
            throw new Exception('La orden N° ' . $orden->id . ' NO corresponde a su sector');
        }
        throw new Exception("La orden no existe en la BD");
    }
}

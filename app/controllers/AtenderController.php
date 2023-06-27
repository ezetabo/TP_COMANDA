<?php
require_once './helpers/helpers.php';

class AtenderController
{

    public static function Atender($request, $response, $args)
    {
        $cookies = $request->getCookieParams();
        $parametros = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $token = $cookies['JWT'];
        $datos = AutentificadorJWT::ObtenerData($token);
        $id_mozo = $datos->id;
        $cookiePedido = $id_mozo . '_Pedidos';
        $duracion = time() + 3600 * 8;
        $mesa = Mesa::obtenerMesa($parametros['id_mesa']);

        if ($mesa->estado == "cerrada") {
            $codigoPedido = generarCodigoAlfanumerico();
            $ped = newPedido(array(
                'codigo_pedido' => $codigoPedido,
                'estado' => 'en curso',
                'id_mesa' => $parametros['id_mesa'],
                'id_mozo' => $id_mozo,
                'importe_final' => 0,
                'foto' => isset($uploadedFiles['foto']) ? $uploadedFiles['foto'] : false,
                'cliente' => $parametros['cliente']
            ));
            $ped->crearPedido();
            $mesa->estado = "con cliente esperando pedido";
            Mesa::modificarMesa($mesa);
            $datos = array('id_mesa' => $mesa->id, 'codigo_pedido' => $codigoPedido);
            $datosSerializados = serialize($datos);
            setcookie($cookiePedido, $datosSerializados, $duracion);
        } else {
            $datosRecuperados = unserialize($_COOKIE[$cookiePedido]);
            if (!isset($_COOKIE[$cookiePedido])) {
                $payload = json_encode(array("mensaje" => 'esa mesa esta siendo atendida por otro mozo'));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json');
            }
            $idMesa = $datosRecuperados['id_mesa'];
            $codigoPedido = $datosRecuperados['codigo_pedido'];
            $pedido = Pedido::obtenerPedidoPorCodigos($idMesa, $codigoPedido);
            if (isset($uploadedFiles['foto'])) {
                $path = './Fotos/';
                $nombre = $datosRecuperados['codigo_pedido'] . '_' . $datosRecuperados['cliente'] . '_' . (new DateTime())->format('Y-m-d_H-i-s') . '.jpg';
                $targetPath = $path . $nombre;
                $pedido->foto = $targetPath;
                F_CTRL::CrearDirectorio($path);
                $uploadedFiles['foto']->moveTo($targetPath);
            }
        }
        $mensaje = 'Ya puede Generar ordenes de preparacion - USE EL CODIGO [ ' . $codigoPedido . ' ]';
        $payload = json_encode(array("mensaje" => $mensaje));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function GenerarOrden($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $orden = newOrden(array(
            'codigo_pedido' => $parametros['codigo_pedido'],
            'producto' => $parametros['producto']
        ));
        $orden->crearOrden();
        $payload = json_encode(array("mensaje" => "Orden generada con exito"));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Todas($request, $response, $args)
    {
        $lista = Orden::obtenerTodos();
        $payload = json_encode(array("listaOrden" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function Pendientes($request, $response, $args)
    {
        $cookies = $request->getCookieParams();
        $token = $cookies['JWT'];
        $datos = AutentificadorJWT::ObtenerData($token);


        if ($datos->rol != 'socio' && $datos->rol != 'mozo') {
            $lista = Orden::obtenerPendientesPorSector($datos->rol);
        } else {
            $lista = Orden::obtenerPorestado('pendiente');
        }
        $payload = json_encode(array("listaOrden" => $lista));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function PrepararOrden($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $cookies = $request->getCookieParams();
        $token = $cookies['JWT'];
        $datos = AutentificadorJWT::ObtenerData($token);
        $orden = Orden::obtenerOrden($parametros['id']);
        if ($orden->estado == 'pendiente') {
            $orden->id_preparador = $datos->id;
            $orden->tiempo_estimado = generarTiempoAleatorio();
            $orden->estado = 'en preparacion';
            Orden::modificarOrden($orden);
            $mensaje = 'La orden paso a preparacion, tiempo estimado: ' . $orden->tiempo_estimado . ' minutos.';
        }else{
            $orden->estado = 'listo para servir';
            $orden->hora_finalizado = date("H:i");
            Orden::modificarOrden($orden);
            $mensaje = 'La orden se encuentra lista para servir.';
        }

        $payload = json_encode(array("mensaje" => $mensaje));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

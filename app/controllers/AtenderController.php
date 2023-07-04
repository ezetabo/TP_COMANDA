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
        $payload = json_encode(array("lista_orden" => $lista));

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
        $payload = json_encode(array("lista_orden" => $lista));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ParaServir($request, $response, $args)
    {
        $lista = Orden::obtenerPorestado('listo para servir');
        $payload = json_encode(array("lista_orden" => $lista));
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
        } else {
            $orden->estado = 'listo para servir';
            $orden->hora_finalizado = date("H:i", strtotime($orden->hora_generado) + ((generarTiempoAleatorio()+ 10 )* 60));
            Orden::modificarOrden($orden);
            $mensaje = 'La orden se encuentra lista para servir.';
        }

        $payload = json_encode(array("mensaje" => $mensaje));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ServirOrden($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $cookies = $request->getCookieParams();
        $token = $cookies['JWT'];
        $mozo = AutentificadorJWT::ObtenerData($token);
        $datosRecuperados = unserialize($_COOKIE[$mozo->id . '_Pedidos']);

        $orden = Orden::obtenerOrden($parametros['id']);
        $mesa = Mesa::obtenerMesa($datosRecuperados['id_mesa']);

        $orden->estado = calcuDiferencia($orden) < 1 ? 'entregado' : 'entregado con demora';
        $mesa->estado = 'con cliente comiendo';

        Mesa::modificarMesa($mesa);
        Orden::modificarOrden($orden);
        $mensaje = 'entrega exitosa, los clientes ya estan comiendo';

        $payload = json_encode(array("mensaje" => $mensaje));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ConsultaCliente($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $tiempoMaximo = Orden::obtenerMayorTiempoEstimado($parametros['codigo_pedido']);
        $ordenes = Orden::obtenerTodosPorPedido($parametros['codigo_pedido']);
        if($tiempoMaximo != 0){            
            $mensaje = array("tiempo aproximado de espera" => $tiempoMaximo.' minutos');
        }else{
            $mensaje = array('estado'=>"Su pedido ya esta listo, esperando por su entrega");
            if(Orden::tieneEstado($ordenes,'pendiente')){
                $mensaje = array('estado'=>"Su pedido esta en lista de espera");
            }
        }
        $payload = json_encode($mensaje); 
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function Cobrar($request, $response, $args)
    {     
        $cookies = $request->getCookieParams();
        $token = $cookies['JWT'];
        $mozo = AutentificadorJWT::ObtenerData($token);
        $datosRecuperados = unserialize($_COOKIE[$mozo->id . '_Pedidos']);                
        $mesa = Mesa::obtenerMesa($datosRecuperados['id_mesa']);
        $pedido = Pedido::obtenerPedidoPorCodigo($datosRecuperados['codigo_pedido']);
        $monto = Producto::obtenerTotalPrecio($datosRecuperados['codigo_pedido']);
        $mesa->estado = "con cliente pagando";
        $pedido->estado = "finalizado";
        $pedido->importe_final = $monto;
        Pedido::modificarPedido($pedido);
        Mesa::modificarMesa($mesa);
        $mensaje = array('Total a pagar'=>$monto);
        $payload = json_encode($mensaje);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function Cerrar($request, $response, $args)
    {            
        $parametros = $request->getQueryParams();                    
        $mesa = Mesa::obtenerMesaPorCodigo($parametros['codigo_mesa']);        
        $mensaje = 'mesa cerrada con exito';
        if($mesa->estado != 'con cliente pagando'){
            $mensaje = 'la mesa no esta lista para cerrar';
        }        
        $payload = json_encode(array('mensaje'=>$mensaje));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function CrearEncuesta($request, $response, $args)
    {            
        $parametros = $request->getParsedBody();
        $encuesta = newEncuesta(array(
            "codigo_pedido" => $parametros['codigo_pedido'],
            "pts_mesa" =>  $parametros['pts_mesa'],
            "pts_restaurante" =>  $parametros['pts_restaurante'],
            "pts_mozo" =>  $parametros['pts_mozo'],
            "pts_cocinero" =>  $parametros['pts_cocinero'],
            "comentario" =>  $parametros['comentario']
        ));
        Encuesta::crearEncuesta($encuesta);   
        $payload = json_encode(array('mensaje'=>'Encuesta generada con exito'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // public function Traercomentarios($request, $response, $args)
    // {
    //     $limite = $request->getAttribute('limite');
    //     $lista = Encuesta::obtenerMejores($limite);
    //     $payload = json_encode(array("mejores_comentarios" => $lista));

    //     $response->getBody()->write($payload);
    //     return $response->withHeader('Content-Type', 'application/json');
    // }

    // public function MesaMasUsada($request, $response, $args)
    // {
    //     $limite = $request->getAttribute('limite');
    //     $lista = Mesa::obtenerMesaMasUsada($limite);
    //     $payload = json_encode(array("mesa_mas_usada" => $lista));

    //     $response->getBody()->write($payload);
    //     return $response->withHeader('Content-Type', 'application/json');
    //}
}

<?php
require_once './models/Usuario.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './models/Orden.php';

function newUsuario($parametros): Usuario
{

    $fecha = new DateTime(date('Y-m-d H:i:s'));
    $usr = new Usuario();
    $usr->mail = $parametros['mail'];
    $usr->clave =  $parametros['clave'];
    $usr->rol =  $parametros['rol'];   
    $usr->estado = isset($parametros['estado']) ? $parametros['estado'] : 'activo';
    $usr->fecha_inicio = date_format($fecha, 'Y-m-d H:i:s');;
    $usr->fecha_fin = null;

    return $usr;
}

function newProducto($parametros): Producto
{
    $usr = new Producto();
    $usr->nombre = $parametros['nombre'];
    $usr->precio =  $parametros['precio'];
    return $usr;
}

function newMesa($parametros): Mesa
{
    $usr = new Mesa();
    $usr->codigo = $parametros['codigo'];
    $usr->estado =  $parametros['estado'];
    return $usr;
}

function newPedido($parametros): Pedido
{
    $usr = new Pedido();
    $usr->codigo_pedido = $parametros['codigo_pedido'];
    $usr->estado =  $parametros['estado'];
    $usr->id_mesa =  $parametros['id_mesa'];
    $usr->id_mozo =  $parametros['id_mozo'];
    $usr->importe_final =  $parametros['importe_final'];
    $usr->foto =  $parametros['foto'];
    return $usr;
}

function newOrden($parametros): Orden
{
    $usr = new Orden();
    $usr->codigo_pedido = $parametros['codigo_pedido'];
    $usr->id_producto =  $parametros['id_producto'];
    $usr->id_preparador =  $parametros['id_preparador'];
    $usr->sector =  $parametros['sector'];
    $usr->tiempo_estimado =  $parametros['tiempo_estimado'];
    $usr->hora_generado =  $parametros['hora_generado'];
    $usr->hora_finalizado =  $parametros['hora_finalizado'];
    $usr->estado =  $parametros['estado'];
    return $usr;
}

// ->add(function ($request, $handler) {
//     return \Logger::ValidarRol($request, $handler, 'socio');
//   });
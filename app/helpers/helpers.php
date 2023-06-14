<?php
require_once './models/Usuario.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './models/Factura.php';

function newUsuario($parametros): Usuario
{

    $fecha = new DateTime(date('Y-m-d H:i:s'));
    $usr = new Usuario();
    $usr->usuario = $parametros['usuario'];
    $usr->clave =  $parametros['clave'];
    $usr->rol =  $parametros['rol'];
    $usr->fecha_inicio = date_format($fecha, 'Y-m-d H:i:s');;
    $usr->fecha_fin = null;

    return $usr;
}

function newProducto($parametros): Producto
{
    $usr = new Producto();
    $usr->nombre = $parametros['nombre'];
    $usr->preparador =  $parametros['preparador'];
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
    $usr->producto = $parametros['producto'];
    $usr->cantidad = $parametros['cantidad'];
    $usr->estado =  $parametros['estado'];
    $usr->id_cliente =  $parametros['id_cliente'];
    $usr->id_preparador =  $parametros['id_preparador'];
    $usr->id_mesa =  $parametros['id_mesa'];
    $usr->id_factura =  $parametros['id_factura'];
    $usr->tiempoPreparacion =  $parametros['tiempoPreparacion'];
    return $usr;
}

function newFactura($parametros): Factura
{
    $usr = new Factura();
    $usr->codigo = $parametros['codigo'];
    $usr->estado =  $parametros['estado'];
    $usr->id_cliente =  $parametros['id_cliente'];
    $usr->foto =  $parametros['foto'];
    $usr->precioTotal =  $parametros['precioTotal'];
    return $usr;
}

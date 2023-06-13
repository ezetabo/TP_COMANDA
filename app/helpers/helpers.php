<?php
require_once './models/Usuario.php';
require_once './models/Producto.php';

function newUsuario($parametros): Usuario{

    $fecha = new DateTime(date('Y-m-d H:i:s'));  
    $usr = new Usuario();
    $usr->usuario = $parametros['usuario'];
    $usr->clave =  $parametros['clave'];
    $usr->rol =  $parametros['rol'];
    $usr->fecha_inicio = date_format($fecha,'Y-m-d H:i:s');;
    $usr->fecha_fin = null;

    return $usr;
}

function newProducto($parametros): Producto{
    $usr = new Producto();
    $usr->nombre = $parametros['nombre'];
    $usr->preparador =  $parametros['preparador'];
    $usr->precio =  $parametros['precio'];  
    return $usr;
}

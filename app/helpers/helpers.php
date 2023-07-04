<?php
require_once './models/Usuario.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './models/Orden.php';
require_once './models/Logs.php';
require_once './models/Estadisticas.php';
require_once './models/Encuesta.php';
require_once 'ManejoArchivos.php';

function newUsuario($parametros): Usuario
{

    $fecha = new DateTime(date('Y-m-d H:i:s'));
    $usr = new Usuario();
    $usr->mail = $parametros['mail'];
    $usr->clave =  $parametros['clave'];
    $usr->rol =  $parametros['rol'];
    $usr->estado = isset($parametros['estado']) ? $parametros['estado'] : 'activo';
    $usr->fecha_inicio = date_format($fecha, 'Y-m-d H:i:s');
    $usr->fecha_fin = null;

    return $usr;
}

function newProducto($parametros): Producto
{
    $usr = new Producto();
    $usr->id = isset($parametros['id']) ? $parametros['id'] : false;
    $usr->nombre = $parametros['nombre'];
    $usr->precio =  $parametros['precio'];
    $usr->sector =  $parametros['sector'];
    return $usr;
}

function newLogs($parametros): Logs
{
    $usr = new Logs();
    $usr->id = isset($parametros['id']) ? $parametros['id'] : false;
    $usr->usuario = $parametros['usuario'];
    $usr->rol =  $parametros['rol'];
    return $usr;
}

function newEncuesta($parametros): Encuesta
{
    $usr = new Encuesta();
    $usr->id = isset($parametros['id']) ? $parametros['id'] : false;
    $usr->codigo_pedido = $parametros['codigo_pedido'];
    $usr->pts_mesa =  $parametros['pts_mesa'];
    $usr->pts_restaurante =  $parametros['pts_restaurante'];
    $usr->pts_mozo =  $parametros['pts_mozo'];
    $usr->pts_cocinero =  $parametros['pts_cocinero'];
    $usr->comentario =  $parametros['comentario'];
    $usr->pts_promedio =  ($usr->pts_mesa  + $usr->pts_restaurante + $usr->pts_mozo + $usr->pts_cocinero) / 4;
    return $usr;
}

function newProductoCSV($data): Producto
{
    $producto = new Producto();
    $producto->id = $data[0];
    $producto->nombre = $data[1];
    $producto->precio = $data[2];
    $producto->sector = $data[3];
    return $producto;
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
    if ($parametros['foto'] != false) {
        $path = './Fotos/';
        $nombre = $parametros['codigo_pedido'] . '_' . $parametros['cliente'] . '_' . (new DateTime())->format('Y-m-d_H-i-s') . '.jpg';
        $targetPath = $path . $nombre;
        $usr->foto = $targetPath;
        F_CTRL::CrearDirectorio($path);
        $parametros['foto']->moveTo($targetPath);
    } else {
        $usr->foto =  $parametros['foto'];
    }
    $usr->cliente =  $parametros['cliente'];
    $usr->fecha = date('Y-m-d H:i:s');
    return $usr;
}

function newOrden($parametros): Orden
{
    $usr = new Orden();
    $usr->codigo_pedido = $parametros['codigo_pedido'];
    $usr->producto =  $parametros['producto'];
    $usr->id_preparador =  0;
    $usr->tiempo_estimado =  0;
    $usr->hora_generado =  date("H:i");
    $usr->hora_finalizado = 0;
    $usr->estado =  'pendiente';
    return $usr;
}

function editarUsuario($id, $parametros): Usuario
{
    try {
        $usr = Usuario::obtenerUsuario($id);
        if (isset($parametros['mail'])) {
            $usr->mail = $parametros['mail'];
        }
        if (isset($parametros['clave'])) {
            $usr->clave = $parametros['clave'];
        }
        if (isset($parametros['rol'])) {
            $usr->rol = $parametros['rol'];
        }
        if (isset($parametros['estado'])) {
            $usr->estado = $parametros['estado'];
        }
        if (isset($parametros['fecha_inicio'])) {
            $usr->fecha_inicio = $parametros['fecha_inicio'];
        }
        if (isset($parametros['fecha_fin'])) {
            $usr->fecha_fin = $parametros['fecha_fin'];
        }

        return $usr;
    } catch (Exception) {
        throw new Exception('Error inesperado al intentar modifcar');
    }
}

function editarProducto($id, $parametros): Producto
{
    try {
        $usr = Producto::obtenerProducto($id);
        if (isset($parametros['nombre'])) {
            $usr->nombre = $parametros['nombre'];
        }
        if (isset($parametros['precio'])) {
            $usr->precio = $parametros['precio'];
        }
        if (isset($parametros['tipo'])) {
            $usr->tipo = $parametros['tipo'];
        }
        return $usr;
    } catch (Exception) {
        throw new Exception('Error inesperado al intentar modifcar');
    }
}

function editarMesa($id, $parametros): Mesa
{
    try {
        $usr = Mesa::obtenerMesa($id);
        if (isset($parametros['codigo'])) {
            $usr->codigo = $parametros['codigo'];
        }
        if (isset($parametros['estado'])) {
            $usr->estado = $parametros['estado'];
        }
        return $usr;
    } catch (Exception) {
        throw new Exception('Error inesperado al intentar modifcar');
    }
}

function editarPedidoPut($id, $parametros): Pedido
{
    try {
        $usr = Pedido::obtenerPedido($id);
        if (isset($parametros['codigo_pedido'])) {
            $usr->codigo_pedido = $parametros['codigo_pedido'];
        }
        if (isset($parametros['estado'])) {
            $usr->estado = $parametros['estado'];
        }
        if (isset($parametros['id_mesa'])) {
            $usr->id_mesa = $parametros['id_mesa'];
        }
        if (isset($parametros['id_mozo'])) {
            $usr->id_mozo = $parametros['id_mozo'];
        }
        if (isset($parametros['importe_final'])) {
            $usr->importe_final = $parametros['importe_final'];
        }
        if (isset($parametros['foto'])) {
            $Base64Img = $parametros['foto'];
            $path = './FotosPedido/';
            $nombreArchivo = (new DateTime())->format('Y-m-d_H-i-s') . '_ID_' . $usr->id . '.jpg';
            $targetPath = $path . $nombreArchivo;
            list(, $Base64Img) = explode(';', $Base64Img);
            list(, $Base64Img) = explode(',', $Base64Img);
            $Base64Img = base64_decode($Base64Img);
            $bckPat = './BackUp/fotos/' . (new DateTime())->format('Y-m-d_H-i-s') . '_ID_' . $usr->id .  '.jpg';
            F_CTRL::CrearDirectorio('./BackUp/fotos/');
            rename($usr->foto, $bckPat);
            $usr->foto = $targetPath;
            file_put_contents($targetPath, $Base64Img);
        }
        if (isset($parametros['cliente'])) {
            $usr->cliente = $parametros['cliente'];
        }
        return $usr;
    } catch (Exception) {
        throw new Exception('Error inesperado al intentar modifcar');
    }
}

function editarPedido($id, $parametros): Pedido
{
    try {
        $usr = Pedido::obtenerPedido($id);
        if (isset($parametros['codigo_pedido'])) {
            $usr->codigo_pedido = $parametros['codigo_pedido'];
        }
        if (isset($parametros['estado'])) {
            $usr->estado = $parametros['estado'];
        }
        if (isset($parametros['id_mesa'])) {
            $usr->id_mesa = $parametros['id_mesa'];
        }
        if (isset($parametros['id_mozo'])) {
            $usr->id_mozo = $parametros['id_mozo'];
        }
        if (isset($parametros['importe_final'])) {
            $usr->importe_final = $parametros['importe_final'];
        }
        if (isset($parametros['foto'])) {
            $targetPath = './FotosPedido/' . (new DateTime())->format('Y-m-d_H-i-s') . '_ID_' . $usr->id . '.jpg';
            $bckPat = './BackUp/fotos/' . (new DateTime())->format('Y-m-d_H-i-s') . '_ID_' . $usr->id .  '.jpg';
            F_CTRL::CrearDirectorio('./BackUp/fotos/');
            rename($usr->foto, $bckPat);
            $usr->foto = $targetPath;
            $parametros['foto']->moveTo($targetPath);
        }
        if (isset($parametros['cliente'])) {
            $usr->cliente = $parametros['cliente'];
        }
        return $usr;
    } catch (Exception) {
        throw new Exception('Error inesperado al intentar modifcar');
    }
}

function generarCodigoAlfanumerico($longitud = 5)
{
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codigo = '';

    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }

    return $codigo;
}

function generarTiempoAleatorio()
{
    $tiempo = rand(5, 45);
    return $tiempo;
}


function calcuDiferencia($datos)
{
    $tiempo1 = strtotime($datos->hora_generado);
    $tiempo2 = strtotime($datos->hora_finalizado);
    $diferencia = abs($tiempo2 - $tiempo1) / 60;

    return $diferencia - $datos->tiempo_estimado;
}

function diferenciaEnMinutos($horaInicio, $horaFin)
{
    $inicio = new DateTime($horaInicio);
    $fin = new DateTime($horaFin);
    $diferencia = $inicio->diff($fin);

    $horasEnMinutos = $diferencia->h * 60;
    $minutos = $diferencia->i;

    $totalMinutos = $horasEnMinutos + $minutos;

    return $totalMinutos;
}

function validarFecha($fecha) {
    $timestamp = strtotime($fecha);
    return $timestamp !== false && $timestamp != -1;
}
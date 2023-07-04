<?php

class Logs
{
    public $id;
    public $fecha;
    public $usuario;
    public $rol;

    public static function crearLogs($datos)
    {
        $fechaActual = date('Y-m-d H:i:s');
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO logs (fecha, usuario, rol) 
                                                       VALUES (:fecha, :usuario, :rol)");
        $consulta->bindValue(':fecha', $fechaActual, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $datos->usuario, PDO::PARAM_INT);
        $consulta->bindValue(':rol', $datos->rol, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, fecha, usuario, rol
                                                       FROM logs");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Logs');
    }
}

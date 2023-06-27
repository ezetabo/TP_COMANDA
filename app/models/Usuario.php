<?php

class Usuario
{
    public $id;
    public $mail;
    public $clave;
    public $rol;
    public $estado;
    public $fecha_inicio;
    public $fecha_fin;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (mail, clave,rol, estado,fecha_inicio,fecha_fin) 
                                                       VALUES (:mail, :clave, :rol, :estado, :fecha_inicio, :fecha_fin)");        
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':mail', $this->mail, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_inicio', $this->fecha_inicio);
        $consulta->bindValue(':fecha_fin', $this->fecha_fin);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, mail, clave, rol, estado, fecha_inicio, fecha_fin
                                                       FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuario($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, mail, clave , rol, estado, fecha_inicio, fecha_fin
                                                        FROM usuarios 
                                                        WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function modificarUsuario($datos)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios 
                                                    SET mail = :mail, clave = :clave, rol = :rol, estado = :estado
                                                    WHERE id = :id");
        $claveHash = password_hash($datos->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':mail', $datos->mail, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->bindValue(':rol', $datos->rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $datos->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $datos->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarUsuario($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios 
                                                    SET fecha_fin = :fecha_fin, estado = :estado
                                                    WHERE id = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':fecha_fin', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->bindValue(':estado', 'baja', PDO::PARAM_STR);
        $consulta->bindValue(':id', $usuario, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function BuscarPorMail($mail)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, mail, clave , rol, estado, fecha_inicio, fecha_fin
                                                        FROM usuarios 
                                                        WHERE mail = :mail");
        $consulta->bindValue(':mail', $mail, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

}

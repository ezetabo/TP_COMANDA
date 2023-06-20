<?php

class Estado_pedido{
    public $id;
    public $descripcion;

    public function crear()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO Estado_pedido (descripcion) 
                                                       VALUES (:descripcion)"); 
        $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);       
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, descripcion
                                                       FROM Estado_pedido");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Estado_pedido');
    }

    public static function obtener($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, descripcion
                                                        FROM Estado_pedido 
                                                        WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Estado_pedido');
    }

    public function modificar()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE Estado_pedido 
                                                    SET descripcion = :descripcion
                                                    WHERE id = :id");       
        $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);       
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrar($descripcion)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE Estado_pedido WHERE id = :id");  
        $consulta->bindValue(':id', $descripcion, PDO::PARAM_INT);       
        $consulta->execute();
    }

}
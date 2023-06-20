<?php

class Encuesta
{
    public $id;
    public $codigo_pedido;    
    public $pts_mesa;
    public $pts_restaurante;   
    public $pts_mozo;
    public $pts_cocinero;    
    public $foto;    

    public function crearEncuesta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO Encuestas (codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, foto)           
            VALUES ( :codigo_pedido, :pts_mesa, :pts_restaurante, :pts_mozo, :pts_cocinero, :foto)"
        );
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':pts_mesa', $this->pts_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':pts_restaurante', $this->pts_restaurante, PDO::PARAM_STR);
        $consulta->bindValue(':pts_mozo', $this->pts_mozo, PDO::PARAM_STR);
        $consulta->bindValue(':pts_cocinero', $this->pts_cocinero, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, foto 
            FROM Encuestas");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }

    public static function obtenerEncuesta($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, foto 
            FROM Encuestas 
            WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }

    public function modificarEncuesta()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta(
            "UPDATE Encuestas 
            SET codigo_pedido = :codigo_pedido, pts_mesa = :pts_mesa, pts_restaurante = :pts_restaurante, pts_mozo = :pts_mozo, pts_cocinero = :pts_cocinero, foto = :foto           
            WHERE id = :id");
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);       
        $consulta->bindValue(':pts_mesa', $this->pts_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':pts_restaurante', $this->pts_restaurante, PDO::PARAM_INT);
        $consulta->bindValue(':pts_mozo', $this->pts_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':pts_cocinero', $this->pts_cocinero, PDO::PARAM_INT);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarEncuesta($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM Encuestas
                                                      WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }   
  
}

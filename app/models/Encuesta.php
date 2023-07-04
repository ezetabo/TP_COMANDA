<?php

class Encuesta
{
    public $id;
    public $codigo_pedido;    
    public $pts_mesa;
    public $pts_restaurante;   
    public $pts_mozo;
    public $pts_cocinero;    
    public $comentario;
    public $pts_promedio;    


    public static function crearEncuesta($datos)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO encuestas (codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, comentario, pts_promedio)           
            VALUES ( :codigo_pedido, :pts_mesa, :pts_restaurante, :pts_mozo, :pts_cocinero, :comentario, :pts_promedio)"
        );
        $consulta->bindValue(':codigo_pedido', $datos->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':pts_mesa', $datos->pts_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':pts_restaurante', $datos->pts_restaurante, PDO::PARAM_STR);
        $consulta->bindValue(':pts_mozo', $datos->pts_mozo, PDO::PARAM_STR);
        $consulta->bindValue(':pts_cocinero', $datos->pts_cocinero, PDO::PARAM_STR);
        $consulta->bindValue(':comentario', $datos->comentario, PDO::PARAM_STR);
        $consulta->bindValue(':pts_promedio', $datos->pts_promedio, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, comentario, pts_promedio
            FROM encuestas");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }
    
    public static function obtenerEncuesta($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, comentario, pts_promedio 
            FROM encuestas 
            WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }

    public static function obtenerEncuestaporCodigo($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, comentario, pts_promedio 
            FROM encuestas 
            WHERE codigo_pedido = :codigo");
            $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Encuesta');
    }

    public function modificarEncuesta()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta(
            "UPDATE encuestas 
            SET codigo_pedido = :codigo_pedido, pts_mesa = :pts_mesa, pts_restaurante = :pts_restaurante, pts_mozo = :pts_mozo, pts_cocinero = :pts_cocinero, comentario = :comentario, pts_promedio = :pts_promedio           
            WHERE id = :id");
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);       
        $consulta->bindValue(':pts_mesa', $this->pts_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':pts_restaurante', $this->pts_restaurante, PDO::PARAM_INT);
        $consulta->bindValue(':pts_mozo', $this->pts_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':pts_cocinero', $this->pts_cocinero, PDO::PARAM_INT);
        $consulta->bindValue(':comentario', $this->comentario, PDO::PARAM_STR);
        $consulta->bindValue(':pts_promedio', $this->pts_promedio, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarEncuesta($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM encuestas
                                                      WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }   
  
    public static function obtenerMejoresComentarios($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, comentario, pts_promedio 
                                                  FROM encuestas
                                                  ORDER BY pts_promedio DESC
                                                  LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }

    public static function obtenerPeoresComentarios($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo_pedido, pts_mesa, pts_restaurante, pts_mozo, pts_cocinero, comentario, pts_promedio 
                                                  FROM encuestas
                                                  ORDER BY pts_promedio ASC
                                                  LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }
}

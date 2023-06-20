<?php

class Orden
{
    public $id;
    public $codigo_pedido;
    public $id_producto;
    public $id_preparador;  
    public $sector;    
    public $tiempo_estimado;    
    public $hora_generado;    
    public $hora_finalizado;    
    public $estado;

    public function crearOrden()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO Ordenes (codigo_pedido, id_producto, id_preparador, sector, tiempo_estimado, hora_generado, hora_finalizado ,estado) 
            VALUES ( :codigo_pedido, :estado, :id_preparador, :id_producto, :sector)"
        );
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':id_producto', $this->id_producto, PDO::PARAM_INT);
        $consulta->bindValue(':id_preparador', $this->id_preparador, PDO::PARAM_INT);       
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);   
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);   
        $consulta->bindValue(':hora_generado', $this->hora_generado, PDO::PARAM_STR);   
        $consulta->bindValue(':hora_finalizado', $this->hora_finalizado, PDO::PARAM_STR);     
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT codigo_pedido, id_producto, id_preparador, sector, tiempo_estimado, hora_generado, hora_finalizado ,estado
            FROM Ordenes");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Orden');
    }

    public static function obtenerOrden($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT codigo_pedido, id_producto, id_preparador, sector, tiempo_estimado, hora_generado, hora_finalizado ,estado
            FROM Ordenes 
            WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Orden');
    }

    public function modificarOrden()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta(
            "UPDATE Ordenes 
            SET codigo_pedido = :codigo_pedido, id_producto = :id_producto, id_preparador = :id_preparador, sector = :sector,
            tiempo_estimado = :tiempo_estimado, hora_generado = :hora_generado, hora_finalizado = :hora_finalizado, estado = :estado
            WHERE id = :id");
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':id_producto', $this->id_producto, PDO::PARAM_INT);
        $consulta->bindValue(':id_preparador', $this->id_preparador, PDO::PARAM_INT);       
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);   
        $consulta->bindValue(':tiempo_estimado', $this->tiempo_estimado, PDO::PARAM_STR);   
        $consulta->bindValue(':hora_generado', $this->hora_generado, PDO::PARAM_STR);   
        $consulta->bindValue(':hora_finalizado', $this->hora_finalizado, PDO::PARAM_STR);     
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarOrden($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM Ordenes
                                                      WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }
}

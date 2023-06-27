<?php

class Orden
{
    public $id;
    public $codigo_pedido;
    public $producto;
    public $id_preparador;
    public $tiempo_estimado;
    public $hora_generado;
    public $hora_finalizado;
    public $estado;

    public function crearOrden()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ordenes (codigo_pedido, producto, id_preparador, tiempo_estimado, hora_generado, hora_finalizado ,estado) 
            VALUES (:codigo_pedido, :producto, :id_preparador, :tiempo_estimado, :hora_generado, :hora_finalizado ,:estado)");
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':producto', $this->producto, PDO::PARAM_STR);
        $consulta->bindValue(':id_preparador', $this->id_preparador, PDO::PARAM_INT);
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
            "SELECT id, codigo_pedido, producto, id_preparador, tiempo_estimado, hora_generado, hora_finalizado ,estado
            FROM ordenes"
        );
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Orden');
    }

    public static function obtenerOrden($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id,codigo_pedido, producto, id_preparador, tiempo_estimado, hora_generado, hora_finalizado ,estado
            FROM ordenes 
            WHERE id = :id"
        );
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Orden');
    }

    public static function modificarOrden($datos)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta(
            "UPDATE ordenes 
            SET codigo_pedido = :codigo_pedido, producto = :producto, id_preparador = :id_preparador,
            tiempo_estimado = :tiempo_estimado, hora_generado = :hora_generado, hora_finalizado = :hora_finalizado, estado = :estado
            WHERE id = :id"
        );
        $consulta->bindValue(':codigo_pedido', $datos->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':producto', $datos->producto, PDO::PARAM_STR);
        $consulta->bindValue(':id_preparador', $datos->id_preparador, PDO::PARAM_INT);
        $consulta->bindValue(':tiempo_estimado', $datos->tiempo_estimado, PDO::PARAM_STR);
        $consulta->bindValue(':hora_generado', $datos->hora_generado, PDO::PARAM_STR);
        $consulta->bindValue(':hora_finalizado', $datos->hora_finalizado, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $datos->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $datos->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarOrden($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM ordenes
                                                      WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerPorestado($estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, producto, id_preparador, tiempo_estimado, hora_generado, hora_finalizado ,estado
            FROM ordenes
            WHERE estado = :estado"
        );
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Orden');
    }

    public static function obtenerPendientesPorSector($sector)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT ordenes.id AS N°orden,codigo_pedido, producto, id_preparador, tiempo_estimado, hora_generado, hora_finalizado ,estado,productos.sector AS sector
            FROM ordenes
            INNER JOIN productos ON ordenes.producto = productos.nombre
            WHERE ordenes.estado = :estado AND productos.sector = :sector"
        );
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->bindValue(':estado', 'pendiente', PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}

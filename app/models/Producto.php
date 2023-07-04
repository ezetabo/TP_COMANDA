<?php

class Producto
{
    public $id;
    public $nombre;
    public $precio;
    public $sector;

    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, precio, sector) 
                                                       VALUES (:nombre, :precio, :sector)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function crearProductos(array $productos)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, precio, sector) 
                                                       VALUES (:nombre, :precio, :sector)");
        foreach ($productos as $producto) {
            $consulta->bindValue(':nombre', $producto->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':precio', $producto->precio, PDO::PARAM_INT);
            $consulta->bindValue(':sector', $producto->sector, PDO::PARAM_STR);
            $consulta->execute();
        }

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, precio, sector
                                                       FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProductoPorNombre($nombre)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, precio, sector
                                                        FROM productos 
                                                        WHERE nombre = :nombre");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function obtenerProducto($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, nombre, precio, sector
                                                        FROM productos 
                                                        WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function modificarProducto($datos)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos 
                                                    SET nombre = :nombre, precio = :precio, sector = :sector
                                                    WHERE id = :id");
        $consulta->bindValue(':nombre', $datos->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $datos->precio, PDO::PARAM_INT);
        $consulta->bindValue(':sector', $datos->sector, PDO::PARAM_STR);
        $consulta->bindValue(':id', $datos->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarProducto($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM productos
                                                      WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerTotalPrecioPorEstado($codigoPedido, $estadoOrden)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(precio)
                                                  FROM productos p
                                                  JOIN ordenes o ON p.id = o.id_producto
                                                  WHERE o.codigo_pedido = :codigo_pedido
                                                  AND o.estado_orden = :estado_orden");
        $consulta->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':estado_orden', $estadoOrden, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn();
    }

    public static function obtenerTotalPrecio($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(precio)
                                                  FROM productos p
                                                  JOIN ordenes o ON p.nombre = o.producto
                                                  WHERE o.codigo_pedido = :codigo_pedido");
        $consulta->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchColumn();
    }
}

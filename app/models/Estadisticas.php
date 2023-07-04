<?php

class Estadisticas
{
    public static function obtenerCantidadOperacionesPorSector()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT p.sector, COUNT(o.id) AS cantidad_operaciones
                                                        FROM ordenes AS o
                                                        INNER JOIN productos AS p ON o.producto = p.nombre
                                                        GROUP BY p.sector ");
       
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerCantidadOperacionesPorSectorPorEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT u.mail AS empleado, p.sector, COUNT(o.id) AS cantidad_operaciones
                                                        FROM ordenes o
                                                        INNER JOIN productos p ON o.producto = p.nombre
                                                        INNER JOIN usuarios AS u ON o.id_preparador = u.id
                                                        GROUP BY u.mail, p.sector");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerCantidadOperacionesPorEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT u.mail AS empleado, COUNT(o.id) AS cantidad_operaciones
                                                        FROM ordenes AS o
                                                        INNER JOIN usuarios AS u ON o.id_preparador = u.id
                                                        GROUP BY u.mail");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerProductoMasVendido($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT o.producto, COUNT(o.id) AS cantidad_vendida
                                                        FROM ordenes o
                                                        WHERE o.estado <> 'cancelado'
                                                        GROUP BY o.producto
                                                        ORDER BY cantidad_vendida DESC
                                                        LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerProductoMenosVendido($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT o.producto, COUNT(o.id) AS cantidad_vendida
                                                        FROM ordenes o
                                                        WHERE o.estado <> 'cancelado'
                                                        GROUP BY o.producto
                                                        ORDER BY cantidad_vendida ASC
                                                        LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerMesaMasUsada($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, COUNT(id_mesa) as total_usos
                                                        FROM pedidos
                                                        GROUP BY id_mesa
                                                        ORDER BY total_usos DESC
                                                        LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerMesaMenosUsada($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, COUNT(id_mesa) as total_usos
                                                        FROM pedidos
                                                        GROUP BY id_mesa
                                                        ORDER BY total_usos ASC
                                                        LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerMesaMayorFacturacion($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, SUM(importe_final) as total_facturacion
                                                        FROM pedidos
                                                        GROUP BY id_mesa
                                                        ORDER BY total_facturacion DESC
                                                        LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerMesaMenorFacturacion($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, SUM(importe_final) as total_facturacion
                                                        FROM pedidos
                                                        GROUP BY id_mesa
                                                        ORDER BY total_facturacion ASC
                                                        LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerMesaMayorImporte($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, importe_final
                                                        FROM pedidos
                                                        WHERE importe_final = (SELECT MAX(importe_final) FROM pedidos)
                                                        LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerMesaMenorImporte($limit)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_mesa, importe_final
                                                        FROM pedidos
                                                        WHERE importe_final = (SELECT MIN(importe_final) FROM pedidos)
                                                        LIMIT :limit");
        $consulta->bindValue(':limit', $limit, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerFacturacionEntreFechas($fechaInicio, $fechaFin)
    {
        $fechaInicioFormatted = date('Y-m-d 00:00:00', strtotime($fechaInicio));
        $fechaFinFormatted = date('Y-m-d 23:59:59', strtotime($fechaFin));
    
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT SUM(importe_final) as total_facturacion
                                                        FROM pedidos
                                                        WHERE fecha >= :fechaInicio AND fecha <= :fechaFin");
        $consulta->bindValue(':fechaInicio', $fechaInicioFormatted, PDO::PARAM_STR);
        $consulta->bindValue(':fechaFin', $fechaFinFormatted, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }
    

   
}

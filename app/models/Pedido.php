<?php

class Pedido
{
    public $id;
    public $codigo_pedido;
    public $estado;
    public $id_mesa;
    public $id_mozo;
    public $importe_final;
    public $foto;
    public $cliente;

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO pedidos (codigo_pedido, estado, id_mesa, id_mozo, importe_final, foto, cliente)           
            VALUES ( :codigo_pedido, :estado, :id_mesa, :id_mozo, :importe_final, :foto, :cliente)"
        );
        $consulta->bindValue(':codigo_pedido', $this->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':id_mozo', $this->id_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':importe_final', $this->importe_final, PDO::PARAM_INT);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':cliente', $this->cliente, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, estado, id_mesa, id_mozo, importe_final, foto, clinete 
            FROM pedidos"
        );
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, estado, id_mesa, id_mozo, importe_final, foto, cliente 
            FROM pedidos 
            WHERE id = :id"
        );
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function modificarPedido($datos)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta(
            "UPDATE pedidos 
            SET codigo_pedido = :codigo_pedido, estado = :estado, id_mesa = :id_mesa, id_mozo = :id_mozo, importe_final = :importe_final, foto = :foto, cliente = :cliente           
            WHERE id = :id"
        );
        $consulta->bindValue(':codigo_pedido', $datos->codigo_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $datos->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $datos->id_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':id_mozo', $datos->id_mozo, PDO::PARAM_INT);
        $consulta->bindValue(':importe_final', $datos->importe_final, PDO::PARAM_INT);
        $consulta->bindValue(':foto', $datos->foto, PDO::PARAM_STR);
        $consulta->bindValue(':cliente', $datos->cliente, PDO::PARAM_STR);
        $consulta->bindValue(':id', $datos->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarPedido($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM pedidos
                                                      WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerPedidoPorCodigos($idMesa, $codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id, codigo_pedido, estado, id_mesa, id_mozo, importe_final, foto, cliente 
            FROM pedidos 
            WHERE id_mesa = :id AND codigo_pedido = :codigo"
        );
        $consulta->bindValue(':id', $idMesa, PDO::PARAM_STR);
        $consulta->bindValue(':codigo', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function obtenerTiempoDemora($codigoMesa, $codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
                                                    "SELECT AVG(ordenes.tiempo_estimado) AS tiempo_demora
                                                    FROM ordenes
                                                    INNER JOIN pedidos ON ordenes.codigo_pedido = pedidos.codigo_pedido
                                                    INNER JOIN mesas ON pedidos.id_mesa = mesas.id
                                                    WHERE mesas.codigo = :codigo_mesa
                                                    AND pedidos.codigo_pedido = :codigo_pedido"
        );
        $consulta->bindValue(':codigo_mesa', $codigoMesa, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_pedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetch(PDO::FETCH_ASSOC)['tiempo_demora'];
    }
}

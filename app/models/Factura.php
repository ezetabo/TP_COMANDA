<?php

class Factura
{
    public $id;
    public $codigo;
    public $estado;
    public $id_cliente;  
    public $foto;
    public $precioTotal;    

    public function crearFactura()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO facturas (codigo, estado, id_cliente, foto, precioTotal) 
            VALUES ( :codigo, :estado, :id_cliente, :foto, :precioTotal)"
        );
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id_cliente', $this->id_cliente, PDO::PARAM_STR);       
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':precioTotal', $this->precioTotal, PDO::PARAM_STR);   
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id,codigo, estado, id_cliente, foto, precioTotal
            FROM facturas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Factura');
    }

    public static function obtenerFactura($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "SELECT id,codigo, estado, id_cliente, foto, precioTotal
            FROM facturas 
            WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Factura');
    }

    public function modificarFactura()
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta(
            "UPDATE facturas 
            SET codigo = :codigo, estado = :estado, id_cliente = :id_cliente, foto = :foto, precioTotal = :precioTotal
            WHERE id = :id");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id_cliente', $this->id_cliente, PDO::PARAM_STR);     
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':precioTotal', $this->precioTotal, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarFactura($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM facturas
                                                      WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }
}

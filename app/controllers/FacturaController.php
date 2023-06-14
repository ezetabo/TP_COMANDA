<?php
require_once './interfaces/IApiUsable.php';
require_once './helpers/helpers.php';

class FacturaController extends Factura implements IApiUsable
{   
    public static function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usr = newFactura($parametros);
        $usr->crearFactura();
        $payload = json_encode(array("mensaje" => "Factura creada con exito"));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $usr = $args['id'];
        $Factura = Factura::obtenerFactura($usr);
        $payload = json_encode($Factura);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Factura::obtenerTodos();
        $payload = json_encode(array("listaFactura" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usr = newFactura($parametros);
        $usr->id = $parametros['id'];
        $usr->modificarFactura();

        $payload = json_encode(array("mensaje" => "Factura modificada con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $FacturaId = $args['id'];
        Factura::borrarFactura($FacturaId);

        $payload = json_encode(array("mensaje" => "Factura borrada con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

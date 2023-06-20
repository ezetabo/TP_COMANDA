<?php
require_once './interfaces/IApiUsable.php';
require_once './helpers/helpers.php';

class OrdenController extends Orden implements IApiUsable
{   
    public static function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usr = newOrden($parametros);
        $usr->crearOrden();
        $payload = json_encode(array("mensaje" => "Orden creada con exito"));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $usr = $args['id'];
        $Orden = Orden::obtenerOrden($usr);
        $payload = json_encode($Orden);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Orden::obtenerTodos();
        $payload = json_encode(array("listaOrden" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usr = newOrden($parametros);
        $usr->id = $parametros['id'];
        $usr->modificarOrden();

        $payload = json_encode(array("mensaje" => "Orden modificada con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $OrdenId = $args['id'];
        Orden::borrarOrden($OrdenId);

        $payload = json_encode(array("mensaje" => "Orden borrada con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

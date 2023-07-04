<?php
require_once './interfaces/IApiUsable.php';
require_once './helpers/helpers.php';

class ProductoController extends Producto implements IApiUsable
{
    public static function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usr = newProducto($parametros);
        $usr->crearProducto();
        $payload = json_encode(array("mensaje" => "Producto creado con exito"));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $usr = $args['id'];
        $Producto = Producto::obtenerProducto($usr);
        $payload = json_encode($Producto);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::obtenerTodos();
        $payload = json_encode(array("lista_producto" => $lista));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public static function ModificarUno($request, $response, $args)
    {

        $usr = newProducto($args['id'], $request->getParsedBody());

        Producto::modificarProducto($usr);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $ProductoId = $args['id'];
        Producto::borrarProducto($ProductoId);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function CargaCsv($request, $response, $args)
    {
        $uploadedFile = $request->getUploadedFiles()['csv'];
        $csvData = $uploadedFile->getStream()->getContents();

        $lista = F_CTRL::cargarObjetosDesdeCSV($csvData, 'newProductoCSV');
         Producto::crearProductos($lista);
    
        $payload = json_encode(array("mensaje" => "Producto creado con éxito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function GenerarCsvOnline($request, $response, $args)
    {
        $csvContent = F_CTRL::GenerarCsvOnline(Producto::obtenerTodos());
        $response = $response->withHeader('Content-Type', 'text/csv');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename="archivo.csv"');
        $response->getBody()->write($csvContent);
        return $response;
    }
}

<?php
require_once './helpers/helpers.php';

class EstadisticasController
{
    public function TraerComentarios($request, $response, $args)
    {
        $limite = $request->getAttribute('limite');
        $tipo = $request->getQueryParams();
        $lista = [];
        $mensaje = 'no se encontraron resultados';
        if ($tipo['tipo'] == 'mejores') {
            $lista = Encuesta::obtenerMejoresComentarios($limite);
            $mensaje = 'mejores_comentarios';
        } else {
            $lista = Encuesta::obtenerPeoresComentarios($limite);
            $mensaje = 'peores_comentarios';
        }
        $payload = json_encode(array($mensaje => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerMesas($request, $response, $args)
    {
        $limite = $request->getAttribute('limite');
        $parametros = $request->getQueryParams();
        switch ($parametros['tipo']) {
            case 'mas_usada':
                $lista = Estadisticas::obtenerMesaMasUsada($limite);
                $mensaje = 'mesa_mas_usada';
                break;
            case 'menos_usada':
                $lista = Estadisticas::obtenerMesaMenosUsada($limite);
                $mensaje = 'mesa_menos_usada';
                break;
            case 'mas_facturo':
                $lista = Estadisticas::obtenerMesaMayorFacturacion($limite);
                $mensaje = 'mesa_que_mas_facturo';
                break;
            case 'menos_facturo':
                $lista = Estadisticas::obtenerMesaMenorFacturacion($limite);
                $mensaje = 'mesa_que_menos_facturo';
                break;
            case 'mayor_importe':
                $lista = Estadisticas::obtenerMesaMayorImporte($limite);
                $mensaje = 'mesa_con_mayor_importe';
                break;
            case 'menor_importe':
                $lista = Estadisticas::obtenerMesaMenorImporte($limite);
                $mensaje = 'mesa_con_menor_importe';
                break;
            case 'facturacion_fechas':
                $lista = Estadisticas::obtenerFacturacionEntreFechas($parametros['fecha_inicio'], $parametros['fecha_fin']);
                $mensaje = 'Facturacion_entre_fechas';
                break;
            default:
                $lista = [];
                $mensaje = 'no se encontraron resultados';
                break;
        }

        $payload = json_encode(array($mensaje => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerPedidos($request, $response, $args)
    {
        $limite = $request->getAttribute('limite');
        $parametros = $request->getQueryParams();      
        switch ($parametros['tipo']) {
            case 'mas_vendido':
                $lista = Estadisticas::obtenerProductoMasVendido($limite);
                $mensaje = 'productos_mas_vendidos';
                break;
            case 'menos_vendido':
                $lista = Estadisticas::obtenerProductoMenosVendido($limite);
                $mensaje = 'productos_menos_vendidos';
                break;
            case 'demorados':
                $lista = Orden::obtenerPorestado('entregado con demora');
                $mensaje = 'pedidos_con_demora';
                break;
            case 'cancelados':
                $lista = Orden::obtenerPorestado('cancelado');
                $mensaje = 'pedidos_cancelados';
                break;
            default:
                $lista = [];
                $mensaje = 'no se encontraron resultados';
                break;
        }

        $payload = json_encode(array($mensaje => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUsuarios($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        switch ($parametros['tipo']) {
            case 'logs':
                $lista = Logs::obtenerTodos();
                $mensaje = 'registro_de_logueos';
                break;
            case 'operaciones_por_sector':
                $lista = Estadisticas::obtenerCantidadOperacionesPorSector();
                $mensaje = 'operaciones_por_sector';
                break;
            case 'operaciones_por_sector_empleado':
                $lista = Estadisticas::obtenerCantidadOperacionesPorSectorPorEmpleado();
                $mensaje = 'operaciones_por_sector_empleado';
                break;
            case 'operaciones_por_empleado':
                $lista = Estadisticas::obtenerCantidadOperacionesPorEmpleado();
                $mensaje = 'operaciones_por_empleado';
                break;
            default:
                $lista = [];
                $mensaje = 'no se encontraron resultados';
                break;
        }

        $payload = json_encode(array($mensaje => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

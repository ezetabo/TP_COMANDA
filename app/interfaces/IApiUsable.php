<?php
interface IApiUsable
{
	public function TraerUno($request, $response, $args);
	public function TraerTodos($request, $response, $args);
	public static function CargarUno($request, $response, $args);
	public function BorrarUno($request, $response, $args);
	public static function ModificarUno($request, $response, $args);
}

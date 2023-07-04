<?php

use Dompdf\Dompdf;

class F_CTRL
{

    public static function CrearDirectorio($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    public static function GuardarFoto($nombre, $tempName, $pathFotos)
    {
        self::CrearDirectorio($pathFotos);
        $rutaCompleta = $pathFotos . $nombre;
        if (move_uploaded_file($tempName, $rutaCompleta)) {
            return $rutaCompleta;
        } else {
            return false;
        }
    }

    public static function MoverFoto($nombre, $pathActual, $nuevoPath)
    {
        self::CrearDirectorio($nuevoPath);
        $nuevaRuta = $nuevoPath . $nombre;
        if (rename($pathActual, $nuevaRuta)) {
            return true;
        }
        return false;
    }

    public static function JsonLeer($pathJson)
    {
        try {
            return json_decode(file_get_contents($pathJson), true);
        } catch (Exception) {
            return false;
        }
    }

    public static function JsonEscribir($datos, $pathJson)
    {
        try {
            self::CrearDirectorio($pathJson);
            file_put_contents($pathJson, json_encode($datos, JSON_PRETTY_PRINT));
            return true;
        } catch (Exception) {
            return false;
        }
    }

    public static function GenerarPDF($datos, $nombre, $path)
    {
        $dompdf = new Dompdf();
        $html = '<h1>Datos</h1>';
        $contador = 1;
        foreach ($datos as $objeto) {
            $html .= '<h2>N° ' . $contador . '</h2>';
            foreach ($objeto as $propiedad => $valor) {
                $html .= '<p style="font-size: 12px;">' . ucfirst($propiedad) . ': ' . $valor . '</p>';
            }
            $html .= '<hr>';
            $contador++;
        }
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfOutput = $dompdf->output();
        self::CrearDirectorio($path);
        return file_put_contents($path . $nombre . '.pdf', $pdfOutput) !== false;
    }

    public static function GenerarPdfOnline($datos)
    {
        $dompdf = new Dompdf();
        $html = '<h1>Datos</h1>';
        $contador = 1;
        foreach ($datos as $objeto) {
            $html .= '<h2>N° ' . $contador . '</h2>';
            foreach ($objeto as $propiedad => $valor) {
                $html .= '<p style="font-size: 12px;">' . ucfirst($propiedad) . ': ' . $valor . '</p>';
            }
            $html .= '<hr>';
            $contador++;
        }
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public static function cargarObjetosDesdeCSV($csvData, $crearObjetoCallback): array
    {
        $objetos = [];
        $lines = explode("\n", $csvData);
        $headers = str_getcsv(array_shift($lines));
        
        foreach ($lines as $line) {
            $data = str_getcsv($line);
            if (count($data) < count($headers)) {
                continue; 
            }
            $objeto = call_user_func($crearObjetoCallback, $data);
            $objetos[] = $objeto;
        }
        
        return $objetos;
    }

    public static function GenerarCsvOnline($datos)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        $archivo = fopen($tempFile, 'w+');  
        $encabezados = array_keys(get_object_vars($datos[0]));
        fputcsv($archivo, $encabezados);    
        foreach ($datos as $objeto) {
            $valores = array_values(get_object_vars($objeto));
            fputcsv($archivo, $valores);
        }    
        fclose($archivo);
        $contenido = file_get_contents($tempFile);
        unlink($tempFile);
    
        return $contenido;
    }
}    

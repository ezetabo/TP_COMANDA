<?php
require_once './models/Usuario.php';
class Check{
    
    public static function ExisteMail($request, $handler){
        $parsedBody = $request->getParsedBody();
        $email = $parsedBody['mail'];
        $usr = Usuario::BuscarPorMail($email);
        if($usr === false){
            return $handler->handle($request);
        }
        throw new Exception("El mail ya existe en la BD");
    }

    public static function Mail($request, $handler){
        $parsedBody = $request->getParsedBody();       
        if(isset($parsedBody['mail']) && !empty($parsedBody['mail'])){
            return $handler->handle($request);
        }
        throw new Exception("El campo mail es requerido y no puede estar vacio");
    }

    public static function Clave($request, $handler){
        $parsedBody = $request->getParsedBody();        
        if(isset($parsedBody['clave']) && !empty($parsedBody['clave'])){
            return $handler->handle($request);
        }
        throw new Exception("El campo clave es requerido y no puede estar vacio");
    }
    public static function Rol($request, $handler){
        $parsedBody = $request->getParsedBody();        
        if(isset($parsedBody['rol']) && !empty($parsedBody['rol'])){
            return $handler->handle($request);
        }
        throw new Exception("El campo rol es requerido y no puede estar vacio");
    }
       
}
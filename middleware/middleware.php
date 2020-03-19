<?php
/*
 * FECHA: 2020/03/18
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: middles.php
 *
 * Descripción: Setea middlewares generales
*/

namespace core;

require (__DIR__."/../core/middleCore.php");

use Exception;

final class MIDDLEWARE extends middleCore
{
    function __construct () {
        //Se debe ejecutar el constructor padre
        parent::__construct ();
    }
    //Clase que carga todos los middleware
    final protected function middlewares () : void {
        //Se revisa que la estructura de la petición sea correcta
        $this->setMiddle (function ($res, $req) {
            if (($req->header["usuario"] ?? "") === "" || ($req->header["session"] ?? "") === "") {
                throw new Exception ("400::La estructura de la petición es incorrecta");
            }
        });
    }
}

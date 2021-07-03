<?php
/*
 * FECHA: 2021/06/25
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: middleware.php
 * 
 * Descripción: Configuración de middlewares
*/

namespace atomic;

require (__DIR__."/../core/middleCore.php");

use http\HTTPException;

final class MIDDLEWARE extends MIDDLECORE
{
    public function __construct () {
        parent::__construct ();
    }

    final protected function setMiddlewares () : void {
        $this->set (function ($res, $req) {
            
        });
    }
}
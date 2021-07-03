<?php
/*
 * FECHA: 2021/06/24
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: index.php
 * 
 * Descripción: Ejecución de API.
*/

namespace atomic;

require ("core/core.php");

use http\{ HTTPException, resolve\RESOLVE };
use Exception;

final class ATOMIC
{
    final public static function main () : void {
        try {
            $core = new CORE;

            $core->load ();

        } catch (HTTPException $e) {
            RESOLVE::jsonResponse ($e->getException ()[0], $e->getException()[2] ?? [ "message" => $e->getException()[1] ], true);
        } catch (Exception $e) {
            $except = HTTPException::parse ($e->getMessage());
            RESOLVE::jsonResponse ($except[0], [ "message" => $except[1] ], true);
        }
    }
}

ATOMIC::main ();
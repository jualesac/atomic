<?php
/*
 * FECHA: 2020/03/17
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: index.php
 *
 * Descripción: Ejecución de API
*/

namespace atomic;

require ("core/core.php");

use core\CORE;
use http\RESOLVE;
use http\HTTPException;
use Exception;

final class ATOMIC
{
    final public static function main () {
        try {
            $core = new CORE;
            //Carga de núcleo de APP
            $core->load ();

        } catch (HTTPException $e) {
            RESOLVE::splResponse ($e->getState (), true);
        } catch (Exception $e) {
            RESOLVE::splResponse (HTTPException::parse ($e->getMessage ()), true);
        }
    }
}

ATOMIC::main ();

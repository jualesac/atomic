<?php
/*
 * FECHA: 2020/03/20
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: initCore.php
 *
 * Descripción: Clase iniciadora de cores
*/

namespace core;

use db\DB;
use file\FILE;

abstract class initCore
{
    protected static $db;

    function __construct () {
        if (!$this::$db) {
            $this::$db = new DB;
        }
    }
}

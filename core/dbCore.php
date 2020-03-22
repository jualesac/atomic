<?php
/*
 * FECHA: 2020/03/20
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: dbCore.php
 *
 * Descripción: Permite iniciar una única instancia hacia la base de datos
*/

namespace core;

require (__DIR__."/../db/db.php");

use db\DB;

abstract class dbCore
{
    protected static $db;

    function __construct () {
        if (!$this::$db) {

            $this::$db = new DB;
        }
    }
}

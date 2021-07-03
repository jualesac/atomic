<?php
/*
 * FECHA: 2021/06/25
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: dbCore.php
 *
 * Descripción: Permite iniciar una única instancia hacia la base de datos
*/

namespace atomic;

require (__DIR__."/../db/db.php");

use atomic\db\DB;

abstract class DBCORE
{
    protected static DB $db;

    public function __construct () {
        if (!isset($this::$db)) {
            $this::$db = new DB;
        }
    }
}
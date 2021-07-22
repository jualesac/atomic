<?php
/*
 * FECHA: 2018/12/26
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jasantos@santander.com.mx | jualesac@yahoo.com
 * TÍTULO: config.php
 *
 * Descripción: Configuración de la base de datos.
*/

namespace atomic\db;

abstract class CONFIG
{
    protected string $host = "localhost";
    protected string $db = "db";
    protected string $user = "user";
    protected string $pass = "pass";
    protected int $port = 3306;
}

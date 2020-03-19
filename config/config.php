<?php
/*
 * FECHA: 2020/03/17
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: config.php
 *
 * Descripción: Configuración general
*/

namespace core;

define ("header", apache_request_headers ());

abstract class CONFIG
{
    protected const UTF8 = false;
    protected $headers = [
        "usuario" => header["usuario"] ?? null,
        "session" => header["session"] ?? null
    ];
}

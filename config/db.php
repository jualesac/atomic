<?php
/*Configuración de la conexión a la base de datos*/

namespace atomic\db;

abstract class CONFIG
{
    protected string $host = "localhost";
    protected string $db = "db";
    protected string $user = "user";
    protected string $pass = "pass";
    protected int $port = 3309;
}

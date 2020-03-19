<?php
/*
 * FECHA: 2020/03/09
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: httpStd.php
 *
 * Descripción: Definición de clases vacías
*/

namespace http;

use stdClass; //Clase estandar de PHP

class HTTPstd extends stdClass
{
    //Se define la llamada de métodos
    public function __call (string $method, array $args) {
        if (isset ($this->$method)) {
            return ($this->$method) (...$args);
        } else {
            throw new HTTPException (404, "Método \"{$method}\" inexistente");
        }
    }
}

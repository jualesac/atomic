<?php
/*
 * FECHA: 2020/03/13
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: route.php
 *
 * Descripción: Objeto para la manipulación del set de rutas
*/
namespace http;

class ROUTE
{
    private $_set;
    private $_middle;

    function __construct (array $set, array $middle) {
        $this->_set = $set;
        $this->_middle = $middle;
    }

    final public function getRoute () {
        $route = current ($this->_set);
        next ($this->_set);

        return $route;
    }

    final public function getMiddlewares () : array {
        return $this->_middle;
    }
}

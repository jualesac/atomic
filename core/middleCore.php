<?php
/*
 * FECHA: 2020/03/17
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jasantos@santander.com.mx | jualesac@yahoo.com
 * TÍTULO: middleCore.php
 *
 * Descripción: Interface para el módulo de configuración de middles.php
*/

namespace core;

abstract class middleCore extends initCore
{
    protected static $p;
    private $middlewares = [];

    function __construct () {
        parent::__construct ();
        
        $this->middlewares ();
    }

    abstract protected function middlewares () : void;

    final protected function setMiddle (callable $middleware, bool $strict = null) {
        $this->middlewares[] = [ $middleware, (($strict === null || $strict === false) ? false : true) ];
    }

    final public function getMiddle () : array {
        return $this->middlewares;
    }
}

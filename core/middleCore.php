<?php
/*
 * FECHA: 2021/06/25
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: middleCore.php
 *
 * Descripción: Clase estandar para la creación de middlewares
*/

namespace atomic;

use SplFixedArray;
use http\HTTPRoute;

abstract class MIDDLECORE extends DBCORE
{
    private HTTPRoute $__route;

    protected function __construct () {
        parent::__construct ();
    }

    abstract protected function setMiddlewares () : void;

    final protected function set (callable $middleware, bool $strict = false) {
        $this->__route->setMiddleware ($middleware, $strict);
    }

    final public function setMiddles (HTTPRoute $route) : void {
        $this->__route = $route;

        $this->setMiddlewares ();
    }
}

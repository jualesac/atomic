<?php
/*
 * FECHA: 2021/06/25
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: stdCore.php
 * 
 * Descripción: Núcleo estandard para controladores
*/

namespace atomic;

require ("dbCore.php");
require ("functionCore.php");
require (__DIR__."/../middleware/middleware.php");

use http\HTTPRoute;
use atomic\db\DB;

use SplFixedArray;

abstract class STDCore extends DBCORE
{
    use FUNCTIONCORE;

    private MIDDLEWARE $middle;
    protected HTTPRoute $route;

    public function __construct () {
        parent::__construct ();

        $this->route = new HTTPRoute;
        $this->middle = new MIDDLEWARE;

        $this->setRoutes ();
        $this->middlewareToRoutes ($this->middle->get());
    }

    abstract protected function setRoutes () : void;

    final public function getRoutes () : HTTPRoute {
        return $this->route;
    }

    private function middlewareToRoutes (SplFixedArray $middle) : void {
        foreach ($middle as $m) {
            $this->route->setMiddleware ($m[0], $m[1]);
        }
    }
}

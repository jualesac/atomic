<?php
/*
 * FECHA: 2020/03/17
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jasantos@santander.com.mx | jualesac@yahoo.com
 * TÍTULO: stdCore.php
 *
 * Descripción: Núcleo estandard para la extensión ejecución de controladores
*/

namespace core;

require ("initCore.php");
require (__DIR__."/../db/db.php");
require (__DIR__."/../file/file.php");
require (__DIR__."/../scheme/scheme.php");
require (__DIR__."/../middleware/middleware.php");

use http\HTTPRoute;

abstract class stdCore extends initCore
{
    protected $middlewares;
    protected $route;

    function __construct () {
        parent::__construct ();

        $this->middlewares = new MIDDLEWARE;
        $this->route = new HTTPRoute;

        $this->middleware (); //Se cargan los middleware generales
        $this->routes (); //Se cargan las rutas de la app
    }

    abstract protected function routes () : void;

    final public function getRoutes () : HTTPRoute {
        return $this->route;
    }

    final private function middleware () : void {
        $middle;

        foreach ($this->middlewares->getMiddle () as $middle) {
            $this->route->setMiddleware ($middle[0], $middle[1]);
        }
    }
}

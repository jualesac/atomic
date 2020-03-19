<?php
/*
 * FECHA: 2020/03/17
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: stdCore.php
 *
 * Descripción: Núcleo estandard para la extensión ejecución de controladores
*/

namespace core;

require (__DIR__."/../middleware/middleware.php");
require (__DIR__."/../db/db.php");
require (__DIR__."/../file/file.php");
require (__DIR__."/../scheme/scheme.php");

use http\HTTPRoute;
use db\DB;
use file\FILE;

abstract class stdCore
{
    protected $middlewares;
    protected $route;
    protected $db;
    protected $file;

    function __construct () {
        $this->middlewares = new MIDDLEWARE;
        $this->route = new HTTPRoute;
        $this->db = new DB;
        $this->file = new FILE;

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

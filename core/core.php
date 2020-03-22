<?php
/*
 * FECHA: 2020/03/17
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: core.php
 *
 * Descripción: Núcleo de framework
*/

namespace core;

require (__DIR__."/../config/config.php");
require (__DIR__."/../config/routes.php");
require (__DIR__."/../http/http.php");
require (__DIR__."/../http/routes/httpRoute.php");

use http\HTTPException;
use http\REQUEST;
use http\HTTP;
use Exception;

final class CORE extends CONFIG
{
    use ROUTES;

    private const _path = __DIR__."/../controllers";
    private $http;
    private $app;

    function __construct () {
        $this->http = new HTTP;
        $this->http->utf8 = $this::UTF8;
        $this->http->headers = $this->headers;

        $this->getApp (); //Se obtiene la app a cargar
    }
    //Se carga la app localizada
    final public function load () {
        $controller;

        if (!$this->app) { throw new HTTPException (404, "Aplicación desconocida"); }
        //Se carga el controlador de app
        require ($this::_path.REQUEST::castUrl($this->app["path"] ?? $this->app[1]));

        if (class_exists ("core\controller")) {
            $controller = new controller;

            //Obtención de ruteo
            $this->http->use (($this->app["uri"] ?? $this->app[0]), $controller->getRoutes ());

            //Si no hay respuesta
            throw new HTTPException (404, "URL no localizada");
        } else {
            throw new HTTPException (500, "No se pudo localizar el controlador de \"".($this->app["uri"] ?? $this->app[0])."\"");
        }
    }
    //Obtención de aplicación
    final private function getApp () {
        $app;
        $path;

        foreach ($this->_PATHS as $app) {
            $path = $app["uri"] ?? $app[0];

            if ($this->http->checkRequest ($path, false)) {
                $this->app = $app; //Se obtiene la app a cargar

                return;
            }

            $path = null;
        }
    }
}

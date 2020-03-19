<?php
/*
 * FECHA: 2020/03/12
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: httpRoute.php
 *
 * Descripción: Organizador de rutas para la clase HTTP
*/

namespace http;

require_once (__DIR__."/../request/request.php");
require ("route.php");

use Exception;

class HTTPRoute
{
    public $_get = [];
    public $_post = [];
    public $_put = [];
    public $_delete = [];
    private $_middle = [ [], [] ];

    private $checkRequest; //Método obtenido en http

    /********** VERBOS **********/
    final public function get (string $url, callable $middle, callable $callback = null) {
        $this->routes ($this->_get, $url, $middle, $callback);
    }

    final public function post (string $url, callable $middle, callable $callback = null) {
        $this->routes ($this->_post, $url, $middle, $callback);
    }

    final public function put (string $url, callable $middle, callable $callback = null) {
        $this->routes ($this->_put, $url, $middle, $callback);
    }

    final public function delete (string $url, callable $middle, callable $callback = null) {
        $this->routes ($this->_delete, $url, $middle, $callback);
    }
    /****************************/

    final public function setContext (HTTP $http) {
        $this->method = $http->method;

        $this->checkRequest = function (string &$url, bool $strict = true) use ($http) : bool {
            return $http->checkRequest ($url, $strict);
        };
    }

    //Retorna los middlewares default aplicables
    final public function getMiddlewares () : array {
        $middle = [];
        $mid;

        foreach ($this->_middle[0] as $mid) {
            if (($this->checkRequest) ($mid[0], $mid[2])) {
                $middle[] = $mid;
            }
        }

        return $middle;
    }

    //Setea un middleware
    final public function setMiddleware ($url, $callback = null, bool $strict = null) {
        //Distribución de argumentos
        if (gettype ($url) !== "string") {
            $strict = $callback;
            $callback = $url;
            $url = "//";
        }

        $strict = ($strict === null || $strict === false) ? false : true;

        if (trim ($url) === "//") {
            $this->_middle[0][] = [ "//", $callback, $strict ];
        } else {
            $this->_middle[1][] = [ trim ($url), $callback, $strict ];
        }
    }


    final public function getRoutes () : ROUTE {
        $mid;
        $middle = [];
        $method = "_".strtolower ($this->method); //Selección de set
        //MIDDLEWARES de ruta
        foreach ($this->_middle[1] as $mid) {
            if (($this->checkRequest) ($mid[0], $mid[2])) {
                $middle[] = $mid;
            }
        }
        //-----------
        return new ROUTE ($this->$method, $middle);
    }

    //Registra una ruta en su colección
    final private function routes (array &$var, string $route, callable $middle, callable $callback = null) {
        //Distribución de argumentos
        if (!$callback) {
            $callback = !$callback ? $middle : $callback;
            $middle = null;
        }

        $var[] = [
            REQUEST::castUrl (trim ($route)),
            $middle,
            $callback
        ];
    }
}

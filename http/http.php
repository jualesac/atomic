<?php
/*
 * FECHA: 2020/03/09
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: http.php
 *
 * Descripción: Envoltura para el manejo de peticiones HTTP
*/

namespace http;

require ("exception/httpException.php");
require ("httpStd.php");
require ("request/request.php");
require ("resolve/resolve.php");

use Exception;

class HTTP
{
    //Parámetros
    private $request;
    private $url_default;
    private $req;
    private $res;
    private $routes; //Utilizada por HTTPRoute

    public $method;
    public $utf8 = false;
    public $headers = [];

    function __construct () {
        if (!isset ($_SERVER["REQUEST_SCHEME"])) { exit ("Se requiere un esquema HTTP"); }

        $this->request = REQUEST::castUrl (REQUEST::rmGets ($_SERVER["REQUEST_URI"]));
        $this->method = $_SERVER["REQUEST_METHOD"];
    }

    //Obtiene la ruta default
    final public function getUrl () : string {
        return $this->url_default;
    }

    //Crea una ruta default o permite un punto de ejecución para rutas
    final public function use (string $url, HTTPRoute $httpRoute = null) {
        if (!isset ($httpRoute)) {
            $this->url_default = REQUEST::castUrl (trim ($url));
            return;
        }

        $route;
        $url = REQUEST::castUrl (trim ($url));
        $httpRoute->setContext ($this);

        try {
            //Ejecución de middlewares generales
            $this->middlewares ($httpRoute->getMiddlewares ());
            $this->routes = $httpRoute->getRoutes (); //Obtiene un objeto ROUTE

            while ($route = $this->routes->getRoute ()) {
                $this->methods ($this->method, $url.$route[0], $route[1] ?? function () {}, $route[2]);
            }

            unset ($this->routes);
        } catch (HTTPException $e) {
            RESOLVE::splResponse ($e->getState (), $this->utf8);
        } catch (Exception $e) {
            RESOLVE::splResponse (HTTPException::parse ($e->getMessage ()), $this->utf8);
        }
    }

    /********** VERBOS **********/
    final public function get (string $url, callable $middle, callable $callback = null) {
        $this->methods ("GET", $url, $middle, $callback);
    }

    final public function post (string $url, callable $middle, callable $callback = null) {
        $this->methods ("POST", $url, $middle, $callback);
    }

    final public function put (string $url, callable $middle, callable $callback = null) {
        $this->methods ("PUT", $url, $middle, $callback);
    }

    final public function delete (string $url, callable $middle, callable $callback = null) {
        $this->methods ("DELETE", $url, $middle, $callback);
    }
    /****************************/
    //Valida la ruta solicitada contra la petición formal, retorna la url por referencia
    final public function checkRequest (string &$url, bool $strict = true) : bool {
        $uri;

        preg_match ("`/(/.{0,})`i", trim ($url), $uri);
        //Se validan las diagonales dobles
        $url = $_SERVER["SCRIPT_NAME"].($uri[1] ?? ($this->url_default.REQUEST::castUrl ($url)));
        $url_pattern = REQUEST::getRegexUrl ($url, $strict);

        if (preg_match ($url_pattern, $this->request)) {
            return true;
        }

        return false;
    }

    //EJECUCIÓN
    final private function methods (string $method, string $url, callable $middle, callable $callback = null) {
        $res;
        $req;
        //Se valida con la petición general
        if (!$this->checkRequest ($url) || ($this->method !== $method)) {
            return;
        }
        //Asignaciones
        if ($callback === null) {
            $callback = $middle;
            $middle = function () { };
        }
        //Ejecuciones
        try {
            $this->res = $res = $this->res ?? new RESOLVE ($this->url_default, $this->headers, $this->utf8);
            $req = new REQUEST ($url);
            //Verificar si existe un punto de entrada HTTPRoute
            if (isset ($this->routes) && get_class ($this->routes) === "http\ROUTE") {
                $this->middlewares ($this->routes->getMiddlewares ());
            }

            $middle ($res, $req); //middleware
            $callback ($res, $req); //Main
            //Liberación de memoria
            $res = null;
            $req = null;
        } catch (HTTPException $e) {
            RESOLVE::splResponse ($e->getState (), $this->utf8);
        } catch (Exception $e) {
            RESOLVE::splResponse (HTTPException::parse ($e->getMessage ()), $this->utf8);
        }
    }

    //Ejecución de Middlewares
    final private function middlewares (array $middlewares) {
        $middle;
        $this->res = $res = $this->res ?? new RESOLVE ($this->url_default, $this->headers, $this->utf8);
        $req;

        foreach ($middlewares as $middle) {
            $req = new REQUEST ($middle[0]);

            $middle[1] ($res, $req);
            $req = null;
        }

        $res = null;
    }
}

<?php
/*
 * FECHA:2021/06/18
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: httpRoute.php
 * 
 * Descripción: Clase para el clasificado de rutas
*/

namespace http;

require ("httpMiddleware.php");

use SplFixedArray;

final class HTTPRoute extends HTTPMiddleware
{
    private SplFixedArray $__routes;
    private string $__method;

    public function __construct () {
        parent::__construct ();

        $this->__routes = new SplFixedArray (0);

        $this->__method = $_SERVER["REQUEST_METHOD"];
    }

    final public function get (string $url, $schema, callable $middle = null, callable $callback = null) : void {
        if ($this->__method !== "GET") { return; }

        $this->route ($url, $schema, $middle, $callback);
    }

    final public function post (string $url, $schema, callable $middle = null, callable $callback = null) : void {
        if ($this->__method !== "POST") { return; }

        $this->route ($url, $schema, $middle, $callback);
    }

    final public function put (string $url, $schema, callable $middle = null, callable $callback = null) : void {
        if ($this->__method !== "PUT") { return; }

        $this->route ($url, $schema, $middle, $callback);
    }

    final public function delete (string $url, $schema, callable $middle = null, callable $callback = null) : void {
        if ($this->__method !== "DELETE") { return; }

        $this->route ($url, $schema, $middle, $callback);
    }

    final public function getRoutes () : SplFixedArray {
        return $this->__routes;
    }

    private function route (string $route, $schema, ...$functions) : void {
        $this->__routes[$this->addArray($this->__routes)] = [
            trim($route),
            $schema,
            $functions[0] ?? null,
            $functions[1] ?? null,
        ];
    }
}
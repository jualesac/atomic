<?php
/*
 * FECHA: 2021/06/25
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: core.php
 * 
 * Descripción: Núcleo de ejecución
*/

namespace atomic;

require (__DIR__."/../config/core.php");
require (__DIR__."/../config/apps.php");
require (__DIR__."/../http/http.php");

require ("stdCore.php");

use http\{ HTTPException, HTTPRoute, request\REQUEST, HTTP };

final class CORE extends CONFIG
{
    use APPS;

    private HTTP $__http;

    public function __construct () {
        $this->__http = new HTTP;
        $this->__http->utf8 = $this::UTF8;
        $this->__http->header = $this->headers;
    }

    final public function load () : void {
        $controller;

        foreach ($this->__packages as $pkg) {
            $uri = $pkg[0] ?? $pkg["uri"];
            $strict = $pkg[2] ?? ($pkg["strict"] ?? false);

            if (!$this->__http->checkRequest($uri, $strict)) {
                continue;
            }

            require ($this::CTRL_PATH.REQUEST::castUrl($pkg[1] ?? $pkg["path"]));

            if (!class_exists("atomic\CONTROLLER")) {
                throw new HTTPException (500, "Unknown Controller");
            }

            $controller = new CONTROLLER;

            $this->__http->use (($pkg[0] ?? $pkg["uri"]), $controller->getRoutes());

            throw new HTTPException (404, "Not Found");
        }

        $this->state404 ();
        //throw new HTTPException (404, "Unknown Application");
    }

    private function state404 () : void {
        header ("HTTP/1.1 404");
        header ("Content-Type: text/html; charset=UTF-8;");

        exit (file_get_contents (__DIR__."/../controllers/404.html"));
    }
}

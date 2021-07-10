<?php
/*
 * FECHA:2021/06/06
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: http.php
 * 
 * Descripción: Envoltura para API REST
*/

namespace http;

require ("exception/httpException.php");
require ("request/request.php");
require ("resolve/resolve.php");
require ("scheme/scheme.php");
require ("route/httpRoute.php");

use http\resolve\RESOLVE;
use http\request\REQUEST;
use http\scheme\SCHEME;
use Exception;

final class HTTP
{
    private string $__request;
    private string $__url = "";
    private object $__req;
    private object $__res;
    private object $__scheme;

    private string $__method;

    public bool $utf8;
    public array $header;

    public function __construct () {
        if (!isset ($_SERVER["REQUEST_SCHEME"])) {
            exit ("Se require un esquema HTTP");
        }

        $this->__request = REQUEST::castUrl (REQUEST::removeGets($_SERVER["REQUEST_URI"]));
        $this->__method = $_SERVER["REQUEST_METHOD"];

        $this->utf8 = false;
        $this->header = [
            "Connection" => "close",
            "Content-Type" => "application/x-www-form-urlencoded"
        ];

        $this->__req = new REQUEST;
        $this->__res = new RESOLVE;
        $this->__scheme = new SCHEME ($this->__req);
    }

    final public function getURL () : string {
        return $this->__url;
    }

    final public function use (string $url, HTTPRoute $routes = null) : void {
        $url = REQUEST::castUrl (trim($url));
        
        $this->__url = $url;
        
        if (!isset($routes)) {
            return;
        }

        if (!preg_match("`^{$url}`i", ($_SERVER["PATH_INFO"] ?? "/"))) {
            return;
        }

        $pkMiddlewares = $routes->getMiddlewares ();
        $pkRoutes = $routes->getRoutes ();

        foreach ($pkMiddlewares as $m) {
            $arg = $this->methodConstruct ($m[0], $m[1], null, null);
            
            $this->methods ($this->__method, $this->__req->castUrl($arg[0]), $arg[1], $arg[2], $arg[3], $m[2]);
        }

        foreach ($pkRoutes as $r) {
            $arg = $this->methodConstruct ($r[0], $r[1], $r[2] ?? null, $r[3] ?? null);
            
            $this->methods ($this->__method, $this->__req->castUrl($arg[0]), $arg[1], $arg[2], $arg[3]);
        }
    }

    final public function get (string $url, $schema, callable $middle = null, callable $callback = null) : void {
        $arg = $this->methodConstruct($url, $schema, $middle, $callback);

        $this->methods ("GET", $arg[0], $arg[1], $arg[2], $arg[3]);
    }

    final public function post (string $url, $schema, callable $middle = null, callable $callback = null) : void {        
        $arg = $this->methodConstruct($url, $schema, $middle, $callback);

        $this->methods ("POST", $arg[0], $arg[1], $arg[2], $arg[3]);
    }

    final public function put (string $url, $schema, callable $middle = null, callable $callback = null) : void {
        $arg = $this->methodConstruct($url, $schema, $middle, $callback);

        $this->methods ("PUT", $arg[0], $arg[1], $arg[2], $arg[3]);
    }

    final public function delete (string $url, $schema, callable $middle = null, callable $callback = null) : void {
        $arg = $this->methodConstruct($url, $schema, $middle, $callback);

        $this->methods ("DELETE", $arg[0], $arg[1], $arg[2], $arg[3]);
    }

    private function methodConstruct ($url, $schema, callable $middle = null, callable $callback = null) : array {
        if (is_callable($schema)) {
            $callback = $middle;
            $middle = $schema;
            $schema = [];
        }

        if ($callback === null) {
            $callback = $middle;
            $middle = function () {};
        }

        return [
            $url,
            $schema,
            $middle,
            $callback
        ];
    }

    private function methods (string $method, string $url, array $schema = [], callable $middle, callable $callback, bool $strict = true) : void {
        if (!$this->checkRequest($url, $strict) || $this->__method !== $method) {
            return;
        }

        try {
            $this->__res->utf8 = $this->utf8;
            $this->__res->redirect->url = $this->__url;
            $this->__res->redirect->header = $this->header;
            $this->__res->httpRequest->url = $this->__url;
            $this->__res->httpRequest->header = $this->header;
            $this->__req->setParams ($url);
            
            if (!$this->__scheme->test ($schema)) {
                return;
            }

            $middle ($this->__res, $this->__req);
            $callback ($this->__res, $this->__req);
            
        } catch (HTTPException $e) {
            RESOLVE::jsonResponse ($e->getException ()[0], $e->getException()[2] ?? [ "message" => $e->getException()[1] ], $this->utf8);
        } catch (Exception $e) {
            $except = HTTPException::parse ($e->getMessage());
            RESOLVE::jsonResponse ($except[0], [ "message" => $except[1] ], $this->utf8);
        }
    }

    final public function checkRequest (string &$url, bool $strict = null) : bool {
        $uri;

        preg_match ("`^/(/.{0,})`i", trim($url), $uri);
        
        $url = $_SERVER["SCRIPT_NAME"].($uri[1] ?? ($this->__url.REQUEST::castUrl($url)));
        $url_regex = REQUEST::castRegexUrl ($url, $strict);
        //var_dump ("\n---{$url}");
        if (preg_match($url_regex, $this->__request)) {
            return true;
        }

        return false;
    }
}

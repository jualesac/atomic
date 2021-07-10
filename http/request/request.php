<?php
/*
 * FECHA: 2021/06/08
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: request.php
 * 
 * Descripción: Clase para obtener valores de una petición HTTP
*/

namespace http\request;

require ("file.php");

final class REQUEST extends FILE
{
    private const _GET = "`((\?|&)[a-z0-9%+_\-.]+=[a-z0-9%+_\-.]+)|(\?|&)`i";
    private const _VAL = "`([a-z0-9%+_\-.]+)=([a-z0-9%+_\-.]+)`i";
    private const _PARAMS = "`/:([a-z0-9_]+)`i";
    
    public array $header;
    public object $get;
    public object $param;
    public object $body;
    public object $file;

    final public function __construct () {
        parent::__construct ();

        $this->header = apache_request_headers ();
        $this->get = $this->getURLValues ();
        $this->body = $this->getBody ();
    }

    final public static function castUrl (string $url) : string {
        return trim (preg_replace(["`^//`", "`^/{0,1}`", "`/$`", "`/\?`", "`^/\+`"], ["/+", "/", "", "?", "//"], trim($url)));
    }

    final public static function removeGets (string $url) : string {
        return preg_replace (self::_GET, "", $url);
    }

    final public static function castRegexUrl (string $url, bool $strict = true) : string {
        return ("`^".preg_replace(["`\.`", self::_PARAMS, "`/$`"], ["\.", "/([a-z0-9%_]+)", ""], self::removeGets($url)).($strict ? "$`i" : "`i"));
    }

    final public function setParams (string $uri) : void {
        $uri = $this->castUrl ($uri);

        $this->param = $this->getParams ($uri);
    }

    private function getURLValues (string $input = null) : object {
        $values = [];

        preg_match_all ($this::_VAL, ($input ?? $_SERVER["REQUEST_URI"]), $values);

        return (object) array_map (function ($v) {
            return trim (urldecode($v));
        }, array_combine($values[1], $values[2]));
    }

    private function getParams (string $uri) : object {
        $keys = [];
        $vals = [];

        preg_match_all ($this::_PARAMS, $uri, $keys);
        preg_match_all ($this::castRegexUrl($this->removeGets($uri)), $this->castUrl($this->removeGets($_SERVER["REQUEST_URI"])), $vals);
        array_shift ($vals);

        return (object) array_combine ($keys[1], array_map(function ($v) {
            return trim (urldecode($v[0]));
        }, $vals));
    }

    private function getBody () : object {
        return (
            (file_get_contents("php://input", false, null, 0, 5) === "")
            || ($_SERVER["REQUEST_METHOD"] === "GET")
        )     ? (object) $_POST : $this->getURLValues (file_get_contents("php://input"));
    }
}
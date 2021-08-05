<?php
/*
 * FECHA: 2021/06/08
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: resolve.php
 * 
 * Descripción: Clase para la resolución de peticiones
*/

namespace http\resolve;

require ("send.php");
require ("stream.php");
require ("redirect.php");
require ("response.php");

final class RESOLVE extends SEND
{
    use RESCONSTRUCT;
    
    public bool $utf8 = false;
    public STREAM $httpRequest;
    public REDIRECT $redirect;

    public function __construct () {
        parent::__construct ();

        $this->httpRequest = new STREAM;
        $this->redirect = new REDIRECT ($this->httpRequest);
    }

    final public static function utf8Encode (string $text) : string {
        if ($text && (mb_detect_encoding($text, "UTF-8", true) !== "UTF-8")) {
            $text = utf8_encode (trim($text));
        }

        return $text;
    }

    final public function response (...$args) : void {
        $this->responseConstruct ($args);
        $this->loadHeaders ();
        $this->jsonResponse ($this->__res[0], $this->__res[1], ($this->__res[2] ?? $this->utf8));
    }

    final public static function jsonResponse (int $state, array $content, bool $utf8 = false) : void {
        $content = $utf8 ? self::utf8EncodeArray($content) : $content;

        header ("HTTP/1.1 {$state}");
        header ("Content-Type: application/json; charset=UTF-8;");

        exit (json_encode($content));
    }

    private static function utf8EncodeArray (array|object $content) : array {
        return array_map (function ($c) {
            if (is_array($c) || is_object($c)) {
                return self::utf8EncodeArray ($c);
            }
            
            return self::utf8Encode ($c);
        }, (is_array($content) ? $content : (array) $content) );
    }
}

<?php
/*
 * FECHA: 2021/07/01
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: send.php
 * 
 * Descripción: Clase para la contestación general
*/

namespace http\resolve;

use SplFixedArray;

abstract class SEND
{
    private static SplFixedArray $__headers;

    protected function __construct () {
        self::$__headers = new SplFixedArray (0);
    }

    final public static function setHeader (array|string $headers) : void {
        if (!isset(self::$__headers)) {
            self::$__headers = new SplFixedArray (0);
        }

        if (is_string($headers)) {
            $headers = [$headers];
        }

        foreach ($headers as $h) {
            if ($h == "" || preg_match("`^HTTP/`i", trim($h))) {
                continue;
            }

            $k = self::$__headers->count ();
            self::$__headers->setSize ($k + 1);
            self::$__headers[$k] = trim ($h);
        }
    }

    final public static function send (int $state, string $content) : void {
        header ("HTTP/1.1 {$state}");

        self::loadHeaders ();

        exit ($content);
    }

    private static function loadHeaders () : void {
        if (!isset(self::$__headers)) {
            self::$__headers = new SplFixedArray (0);
        }

        foreach (self::$__headers as $h) {
            header ($h);
        }
    }
}

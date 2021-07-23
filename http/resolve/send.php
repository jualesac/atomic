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

use HTTPException;
use SplFixedArray;

abstract class SEND
{
    private SplFixedArray $__headers;

    protected function __construct () {
        $this->__headers = new SplFixedArray (0);
    }

    final public function setHeader (array|string $headers) : void {
        if (is_string($headers)) {
            $headers = [$headers];
        }

        foreach ($headers as $h) {
            if ($h == "" || preg_match("`HTTP/`i", $h)) {
                continue;
            }

            $k = $this->__headers->count ();
            $this->__headers->setSize ($k + 1);
            $this->__headers[$k] = $h;
        }
    }

    final public function send (int $state, string $content) : void {
        header ("HTTP/1.1 {$state}");
        
        $this->loadHeaders ();

        echo ($content);
        
        exit;
    }

    final protected function loadHeaders () : void {
        foreach ($this->__headers as $h) {
            header ($h);
        }
    }
}

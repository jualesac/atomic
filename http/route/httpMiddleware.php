<?php
/*
 * FECHA:2021/06/23
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: httpMiddleware.php
 * 
 * Descripción: Clase para el clasificado de middlewares
*/

namespace http;

use SplFixedArray;

abstract class HTTPMiddleware
{
    private SplFixedArray $__middleMain;
    private SplFixedArray $__middle;

    protected function __construct () {
        $this->__middleMain = new SplFixedArray (0);
        $this->__middle = new SplFixedArray (0);
    }

    final public function setMiddleware (string|callable $url, callable|bool $callback = null, bool $strict = false) : void {
        if (is_callable($url)) {
            $strict = $callback;
            $callback = $url;
            $url = "//";
        }

        $strict = ($strict === null || $strict === false) ? false : true;

        $f = (trim($url) === "//") ? true : false;
        
        if ($f) {
            $this->__middleMain[$this->addArray($this->__middleMain)] = [ trim($url), $callback, $strict ];
        } else {
            $this->__middle[$this->addArray($this->__middle)] = [ trim($url), $callback, $strict ];
        }
    }

    final public function getMiddlewares () : array {
        return array_merge ($this->__middleMain->toArray(), $this->__middle->toArray());
    }

    final protected function addArray (SplFixedArray $splArray) : int {
        $k = $splArray->count ();

        $splArray->setSize ($k + 1);

        return $k;
    }
}

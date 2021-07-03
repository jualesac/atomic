<?php
/*
 * FECHA: 2021/06/25
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: middleCore.php
 *
 * Descripción: Clase estandar para la creación de middlewares
*/

namespace atomic;

use SplFixedArray;

abstract class MIDDLECORE extends DBCORE
{
    private SplFixedArray $__middle;

    public function __construct () {
        parent::__construct ();

        $this->__middle = new SplFixedArray (0);

        $this->setMiddlewares ();
    }

    abstract protected function setMiddlewares () : void;

    final protected function set (callable $middleware, bool $strict = false) {
        $k = $this->__middle->count ();

        $this->__middle->setSize ($k + 1);
        $this->__middle[$k] = [ $middleware, $strict ];
    }

    final public function get () : SplFixedArray {
        return $this->__middle;
    }
}
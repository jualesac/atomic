<?php
/*
 * FECHA: 2021/06/08
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: response.php
 * 
 * Descripción: Clase constructora para respuestas
*/

namespace http\resolve;

final class RESPONSE
{
    public int $__state;
    public array $__content;
    public bool $__utf8;
    public bool $__utf8isNull = true;

    final public function __construct ($args) {
        if (is_array($args[1] ?? null)) {

            $this->__utf8isNull = isset($args[2]) ? false : true;
            $this->__construct0 ($args[0], $args[1], ( $args[2] ?? false ));

        } elseif (is_array($args[0])) {

            $this->__utf8isNull = isset($args[1]) ? false : true;
            $this->__construct1 ($args[0], ( $args[1] ?? false ));

        } elseif (is_int((int) $args[0]) && !is_null($args[1])) {
            
            $this->__utf8isNull = isset($args[2]) ? false : true;
            $this->__construct2 ($args[0], $args[1], ( $args[2] ?? false ));
            
        }
    }

    private function __construct0 (int $a, array $b, bool $c = false) : void {
        $this->__state = $a;
        $this->__content = $b;
        $this->__utf8 = $c;
    }

    private function __construct1 (array $a, bool $b = false) : void {
        $this->__state = (int) ($a["state"] ?? $a[0]);
        $this->__content = $this->arrayEncode ( $a["content"] ?? ($a["message"] ?? $a[1]) );
        $this->__utf8 = $b;
    }

    private function __construct2 (int $a, $b, bool $c = false) : void {
        $this->__state = $a;
        $this->__content = $this->arrayEncode ($b);
        $this->__utf8 = $c;
    }

    private function arrayEncode ($content) : array {
        if (is_array($content)) { return $content; }
        if (is_object($content)) { return (array) $content; }

        return [ "message" => $content ];
    }
}
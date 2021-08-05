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

trait RESCONSTRUCT
{
    private array $__res;

    private function responseConstruct (array $args) : void {
        if (is_array($args[1] ?? null)) {
            $this->__resConstruct0 ($args[0], $args[1], ($args[2] ?? null));
        } elseif (is_array($args[0])) {
            $this->__resConstruct1 ($args[0], ($args[1] ?? null));
        }elseif (is_int((int) $args[0]) && !is_null($args[1])) {
            $this->__resConstruct2 ($args[0], $args[1], ($args[2] ?? null));
        }
    }

    private function __resConstruct0 (int $a, array $b, bool $c = null) : void {
        $this->__res = [ $a, $b, $c ];
    }

    private function __resConstruct1 (array $a, bool $b = null) : void {
        $this->__res = [
            (int) ($a["state"] ?? $a[0]),
            $this->__arrayEncode ($a["content"] ?? ($a["message"] ?? $a[1])),
            $b
        ];
    }

    private function __resConstruct2 (int $a, $b, bool $c = null) : void {
        $this->__res = [
            (int) $a,
            $this->__arrayEncode ($b),
            $c
        ];
    }

    private function __arrayEncode ($c) : array {
        if (is_array($c)) { return $c; }
        if (is_object($c)) { return (array) $c; }

        return [ "message" => $c ];
    }
}

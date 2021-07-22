<?php
/*
 * FECHA: 2021/06/09
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: httpException.php
 *
 * Descripción: Manejador de excepciones.
*/

namespace http;

use Exception;

final class HTTPException extends Exception
{
    private const __default = [
        "state" => 418,
        "message" => "Generic error"
    ];

    private int $__state;
    private string $__message;
    private array $__content;

    final public function __construct (...$args) {
        if ((is_int($args[0]) || is_string($args[0])) && is_string($args[1])) {
            $this->construct0 ((int) $args[0], $args[1]);
        } elseif (is_array($args[0])) {
            $this->construct1 ($args[0]);
        } elseif ((is_int($args[0]) || is_string($args[0])) && is_array($args[1])) {
            $this->construct2 ($args[0], $args[1]);
        } else {
            $this->construct3 ();
        }

        parent::__construct ($this->__message);
    }

    private function construct0 (int $a, string $b) : void {
        $this->__state = $a;
        $this->__message = $b;
    }

    private function construct1 (array $a) : void {
        $this->__state = (int) ($a["state"] ?? $a[0]);
        $this->__message = ($a["message"] ?? $a[1]);
    }

    private function construct2 (int $a, array $b) : void {
        $this->__state = $a;
        $this->__content = $b;
        $this->__message = "";
    }

    private function construct3 () : void {
        $this->__state = $this->__default["state"];
        $this->__message = $this->__default["message"];
    }

    final public function __toString () : string {
        return "{$this->__state}::{$this->__message}";
    }

    final public function getException () : array {
        return [
            $this->__state,
            $this->__message,
            $this->__content ?? null,
            "state" => $this->__state,
            "message" => $this->__message,
            "content" => $this->__content ?? null
        ];
    }

    final public static function parse (string $message) : array {
        $a = explode ("::", $message);

        if (count($a) < 2) {
            return [ 500, "[FAILED TO GET STATUS]::{$message}"];
        }

        return $a;
    }
}

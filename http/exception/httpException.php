<?php
/*
 * FECHA: 2020/03/09
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
    private const DEFAULT_STATE = [
        "state" => 418,
        "message" => "Imposible hacer el café, soy una tetéra"
    ];
    private $_state;

    public function __construct ($state = null, string $message = null) {
        $cols = [ "state", "message" ];

        if (is_array ($state)) {
            $this->_state = array_combine ($cols, $state);
        } else {
            $this->_state = array_combine ($cols, [ $state ?? $this::DEFAULT_STATE["state"], $message ?? $this::DEFAULT_STATE["message"] ]);
        }
        //Se llama al constructor padre
        parent::__construct ($this->_state["message"]);
    }

    public function __toString () {
        return "{$this->_state["state"]}::{$this->_state["message"]}";
    }


    final public function getState () : array {
        $state = [];
        $k; $v;

        foreach ($this->_state as $k => $v) {
            $state[] = $v;
            $state[$k] = $v;
        }

        return $state;
    }

    //Convierte una cadena del tipo "183::Mensaje" en un array
    final public static function parse (string $message) : array {
        $array = explode ("::", $message);

        if (count ($array) === 1) {
            $array = [
                500,
                "[FAILED TO GET STATUS]:: {$message}"
            ];
        }

        return $array;
    }
}

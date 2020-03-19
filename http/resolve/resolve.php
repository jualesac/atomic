<?php
/*
 * FECHA: 2020/03/09
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: response.php
 *
 * Descripción: Clase para las respuestas HTTP
*/

namespace http;

require ("stream.php");

class RESOLVE extends STREAM
{
    private $COD_UTF8;

    function __construct (string $url = null, array $headers = [], bool $utf8 = null) {
        parent::__construct ($url, $headers);

        $this->COD_UTF8 = $utf8;
    }

    //Codifica una cadena en utf8
    final public static function utf8 (string $text) : string {
        if ($text && (mb_detect_encoding ($text, "UTF-8", true) !== "UTF-8")) {
            $text = utf8_encode (trim ($text));
        }

        return $text;
    }

    //Respuesta simple en JSON
    final public static function splResponse (array $response, bool $utf8 = null) {
        $res["message"] = $utf8 ? self::utf8 ($response[1]) : $response[1];

        header ("HTTP/1.1 {$response[0]}");
        header ("Content-Type: application/json; charset = utf-8");

        exit (json_encode ($res));
    }

    //Se encarga de codificar en utf8 todo el contenido de un array/objeto
    final private function encodeUTF8 ($response) : array {
        return array_map (function ($cell) {
            $cod;

            if (is_array ($cell) || is_object ($cell)) {
                $cod = $this->encodeUTF8 ($cell);
            } else {
                $cod = $this::utf8 ($cell);
            }

            return $cod;
        }, (array) $response);
    }

    //Respuesta general en JSON
    final public function response ($state, $message, $utf8 = null) {
        if (is_array ($state)) {
            $codifing = ((boolean) $message ?? null) ?? $this->COD_UTF8;
            $message = $state["message"] ?? $state[1];
            $state = $state["state"] ?? $state[0];
        }

        $response;
        //La respuesta siempre debe ser un array
        if (is_object ($message)) {
            $message = (array) $message;
        }

        if (!is_array ($message)) {
            $response["message"] = $message;
        }

        $response = $response ?? $message;

        header ("HTTP/1.1 {$state}");
        header ("Content-Type: application/json; charset = utf-8");

        exit (json_encode (($utf8 ?? $this->COD_UTF8) === true ? $this->encodeUTF8 ($response) : $response));
    }

    //Método para redireccionar, la redirección siempres es por verbo GET
    final public function redirect (string $ruta) {
        header ("Status: 302 Moved Permanently", false, 302);
        header ("Location: {$ruta}");
        exit ();
    }
}

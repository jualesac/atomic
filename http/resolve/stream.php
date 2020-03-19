<?php
/*
 * FECHA: 2020/03/09
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: stream.php
 *
 * Descripción: Envoltura de peticiones
*/

namespace http;

use Exception;

class STREAM extends HTTPstd
{
    //Propiedades default
    private $_url;
    private $_headers;
    //Closure request
    public $request;

    function __construct (string $url = null, array $headers = []) {
        //Se obtiene la url default sin params
        $this->_url = str_replace (":", "", $url ?? "");
        $this->_headers = $this->headerToString ($headers);

        $this->clsreReq ();
    }

    /********** VERBOS **********/
    final private function clsreReq () {
        $this->request = new HTTPstd;

        $this->request->get = function (string $url, $data = null, array $headers = []) : object {
            return $this->stream ("GET", $url, $data, $headers);
        };

        $this->request->post = function (string $url, $data = null, array $headers = []) : object {
            return $this->stream ("POST", $url, $data, $headers);
        };

        $this->request->put = function (string $url, $data = null, array $headers = []) : object {
            return $this->stream ("PUT", $url, $data, $headers);
        };

        $this->request->delete = function (string $url, $data = null, array $headers = []) : object {
            return $this->stream ("DELETE", $url, $data, $headers);
        };
    }
    /****************************/

    //Manda una petición hacia otro ruteo configurado
    final private function stream (string $method, string $url, $data = null, array $headers = []) : object {
        $_proto = "http";
        $url_ = $this->_url;
        $wrapper;
        $context;
        $resolve;
        $resHead;
        $return;

        if (isset ($_SERVER["HTTPS"])) {
            $_proto = "https";
        }
        //Permitir rutas ignorando la default
        if (preg_match ("`^//`", $url)) {
            $url_ = "";
        }

        $uri = "{$_proto}://".$_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"].$url_.preg_replace (["`^/{0,2}`", "`/$`", "`/\?`"], ["/", "", "?"], $url);

        try {
            $data = http_build_query ($data ?? []);

            $wrapper = [
                $_proto => [
                    "method" => $method,
                    "header" => $this->_headers.$this->headerToString ($headers)."Connection: close\r\nContent-Type: application/x-www-form-urlencoded\r\n",
                    "content" => $data
                ]
            ];

            $context = stream_context_create ($wrapper);
            if (@!($resolve = file_get_contents ($uri, false, $context))) { throw new Exception ("La request regresó un estado de error"); }
            $resHead = $resolve ? $http_response_header : [];
        } catch (Exception $e) {
            throw new HTTPException (500, $e->getMessage ());
        }

        return $this->stdReturn ($resHead, $resolve);
    }

    //Devuelve un objeto httstd como respuesta
    final private function stdReturn (array $headers, string $resolve) : object {
        $obj = new HTTPstd;
        $state;
        $header = [];
        //Se obtiene el estado
        if (count ($headers)) { preg_match ("`HTTP/\d+\.[0-9\.]+ (\d+)`", $headers[0], $state); }
        array_shift ($headers);
        //Se obtienen las cabeceras
        array_map (function ($head) use (&$header) {
            $h = explode (":", $head);

            $header[$h[0]] = trim ($h[1]);
        }, $headers);

        $obj->header = $header;
        $obj->state = $state[1] ?? 404;
        $obj->reply = json_decode (urldecode ($resolve)) ?? $resolve;

        return $obj;
    }

    //Regresa un string con los headers
    final private function headerToString (array $headers = []) : string {
        $header;
        $head;
        $hString = "";

        foreach ($headers as $head => $header) {
            $hString .= "{$head}: ".trim ($header)."\r\n";
        }

        return $hString;
    }
}

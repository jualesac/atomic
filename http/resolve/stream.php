<?php
/*
 * FECHA: 2021/06/10
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: stream.php
 *
 * Descripción: Envoltura de peticiones
*/

namespace http\resolve;

use Exception;

final class STREAM
{
    public string $hots;
    public string $script;
    public string $url;
    public bool $secureProtocol;
    public array $header;
    public int $state;
    public string $content;

    public function __construct () {
        $this->host = $_SERVER["HTTP_HOST"];
        $this->script = $_SERVER["SCRIPT_NAME"];
        $this->url = "";
        $this->secureProtocol = false;
        $this->header = [
            "Connection" => "close",
            "Content-Type" => "aaplication/x-www-form-urlencoded"
        ];
        $this->content = "";
    }

    final public function get (string $url) : void {
        $this->stream ("GET", $url);
    }

    final public function post (string $url, $body = []) : void {
        $this->stream ("POST", $url, $body);
    }

    final public function put (string $url, $body = []) : void {
        $this->stream ("PUT", $url, $body);
    }

    final public function delete (string $url, $body = []) : void {
        $this->stream ("DELETE", $url, $body);
    }

    private function stream (string $method, string $url, $body = []) : void {
        $proto = $this->secureProtocol ? "https" : "http";
        $_url = preg_match("`^//`", $url) ? "" : $this->url;
        $state;
        $h;

        $uri = "{$proto}://{$this->host}{$this->script}{$_url}".preg_replace (["`^/{0,2}`", "`/$`", "`/\?`"], ["/", "", "?"], $url);

        $data = http_build_query ($body ?? []);

        $wrapper = [
            $proto => [
                "method" => $method,
                "header" => $this->headerToString($this->header),
                "content" => $data
            ]
        ];

        $context = stream_context_create ($wrapper);

        if (@!$this->content = file_get_contents($uri, false, $context)) {
            $this->state = 404;
            return;
        }

        $h = $http_response_header ?? [];

        if (count ($h) > 0) {
            preg_match ("`HTTP/[0-9.]+ (\d+)`", $h[0], $state);

            $this->state = (int) $state[1];
        } else {
            $this->state = 500;
        }
    }

    private function headerToString (array $headers = []) : string {
        $header = "";

        foreach ($headers as $k => $h) {
            $header .= "{$k}: ".trim($h)."\r\n";
        }

        return $header;
    }
}
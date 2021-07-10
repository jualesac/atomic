<?php
/*
 * FECHA: 2021/06/11
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: redirect.php
 *
 * Descripción: Clase para redirecciones
*/

namespace http\resolve;

final class REDIRECT
{
    private STREAM $httpRequest;

    public string $url;
    public array $header;

    public function __construct (STREAM $httpRequest) {
        $this->httpRequest = $httpRequest;
        $this->url = "";
    }

    final public function get (string $url) : void {
        $this->__red (function () use ($url) {
            $this->httpRequest->get ($url);
        
            $this->response ($this->httpRequest->state, $this->httpRequest->content);
        });
    }

    final public function post (string $url, $body = []) : void {
        $this->__red (function () use ($url, $body) {
            $this->httpRequest->post ($url, $body);
        
            $this->response ($this->httpRequest->state, $this->httpRequest->content);
        });
    }

    final public function put (string $url, $body = []) : void {
        $this->__red (function () use ($url, $body) {
            $this->httpRequest->put ($url, $body);
        
            $this->response ($this->httpRequest->state, $this->httpRequest->content);
        });
    }

    final public function delete (string $url, $body = []) : void {
        $this->__red (function () use ($url, $body) {
            $this->httpRequest->delete ($url, $body);
        
            $this->response ($this->httpRequest->state, $this->httpRequest->content);
        });
    }

    private function __red (callable $callback) : void {
        $this->httpRequest->host = $_SERVER["HTTP_HOST"];
        $this->httpRequest->script = $_SERVER["SCRIPT_NAME"];
        $this->httpRequest->url = $this->url;
        $this->httpRequest->secureProtocol = isset($_SERVER["HTTPS"]) ? true : false;
        $this->httpRequest->header = $this->header;

        $callback ();
    }

    private function response (int $state, string $content) : void {
        header ("HTTP/1.1 {$state}");
        header ("Content-Type: application/json; charset=UTF-8;");

        exit ($content);
    }
}
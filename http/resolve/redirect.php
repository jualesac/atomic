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

final class REDIRECT extends SEND
{
    private STREAM $httpRequest;

    public string $url;
    public array $header;

    public function __construct (STREAM $httpRequest) {
        parent::__construct ();

        $this->httpRequest = $httpRequest;
        $this->url = "";
    }

    final public function get (string $url) : void {
        $this->__red (function () use ($url) {
            $this->httpRequest->get ($url);
        });
    }

    final public function post (string $url) : void {
        $this->__red (function () use ($url) {
            $this->httpRequest->post ($url);
        });
    }

    final public function put (string $url) : void {
        $this->__red (function () use ($url) {
            $this->httpRequest->put ($url);
        });
    }

    final public function delete (string $url) : void {
        $this->__red (function () use ($url) {
            $this->httpRequest->delete ($url);
        });
    }

    private function __red (callable $callback) : void {
        $this->httpRequest->host = $_SERVER["HTTP_HOST"];
        $this->httpRequest->script = $_SERVER["SCRIPT_NAME"];
        $this->httpRequest->url = $this->url;
        $this->httpRequest->secureProtocol = isset($_SERVER["HTTPS"]) ? true : false;
        $this->httpRequest->header = $this->header;

        $callback ();

        parent::setHeader ("Content-Type: application/json; charset=UTF-8;");
        parent::send ($this->httpRequest->state, $this->httpRequest->content);
    }
}

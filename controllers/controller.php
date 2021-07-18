<?php
/*
 * FECHA: 2021/06/25
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: controller.php
 * 
 * Descripción: Controlador
*/

namespace atomic;

final class CONTROLLER extends STDCore
{
    public function __construct () {
        parent::__construct ();
    }

    final protected function setRoutes () : void {
        $this->route->get ("/", function ($res, $req) {
            $res->response (200, $req);
        });

        $this->route->get ("/SALUDO", function ($res, $req) {

            $html = <<<HTML
                <html>
                    <head>
                    </head>

                    <body>
                        <h1>Hola {$req->get->nombre}</h1>
                    </body>
                </html>
HTML;
            $res->setHeader ("Content-Type: text/html; charset = utf-8;");
            $res->send (201, $html);
        });

        $this->route->post ("/", [
            "body" => [
                "campo" => "number"
            ]
        ], function ($res, $req) {
            $res->response (200, $req);
        });

        $this->route->put ("/", function ($res, $req) {
            $res->response (200, $req);
        });

        $this->route->delete ("/", function ($res, $req) {
            $res->response (200, $req);
        });
    }
}

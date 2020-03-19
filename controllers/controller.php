<?php
/*
 * FECHA: 2020/03/17
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: controller.php
 *
 * Descripción: Controlador de prueba
*/

namespace core;

require (__DIR__."/../core/stdCore.php");

final class CONTROLLER extends stdCore
{
    function __construct () {
        //Se debe ejecutar el constructor padre
        parent::__construct ();
    }

    final protected function routes () : void {
        $this->route->get ("PRUEBA", function ($res, $req) {
            $res->response (200, $res->request->post ("PRUEBA"));
        });

        $this->route->post ("PRUEBA", function ($res, $req) {
            $res->response (201, ["header" => $req->header]);
        });
    }
}

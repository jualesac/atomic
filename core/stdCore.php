<?php
/*
 * FECHA: 2021/06/25
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: stdCore.php
 * 
 * Descripción: Núcleo estandard para controladores
*/

namespace atomic;

require ("dbCore.php");
require ("functionCore.php");
require ("middleCore.php");
require (__DIR__."/../middleware/middleware.php");
require (__DIR__."/../ext/scheme/scheme.php");
require (__DIR__."/../ext/file/file.php");
require (__DIR__."/../ext/sftp/sftp.php");
require (__DIR__."/../ext/zip/zip.php");

use http\HTTPRoute;
use atomic\db\DB;

abstract class STDCore extends DBCORE
{
    use FUNCTIONCORE;

    protected HTTPRoute $route;

    protected function __construct () {
        parent::__construct ();

        $this->route = new HTTPRoute;
        $middle = new MIDDLEWARE;

        $this->setRoutes ();
        $middle->setMiddles ($this->route);
    }

    abstract protected function setRoutes () : void;

    final public function getRoutes () : HTTPRoute {
        return $this->route;
    }
}

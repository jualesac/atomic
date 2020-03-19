<?php
/*
 * FECHA: 2020/03/09
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: request.php
 *
 * Descripción: Clase para obtener valores de una petición HTTP
*/

namespace http;

require ("file.php");

class REQUEST extends FILE
{
    private const GET_PATTERN = "`((\?|&)[a-z0-9%+_\-.]+=[a-z0-9%+_\-.]+)|(\?|&)`i";
    private const VAL_PATTERN = "`([a-z0-9%+_\-.]+)=([a-z0-9%+_\-.]+)`i";
    private const PARAMS_PATTERN = "`/:([a-z0-9_]+)`i";

    private $url;

    public $header;
    public $get;
    public $param;
    public $body;
    public $file;
    //La URL en este caso es una ruta personalizada que se comparará con la petición real
    function __construct (string $url) {
        parent::__construct ();

        $this->url = $this->castUrl ($url);

        $this->header = apache_request_headers ();
        $this->get = $this->getValuesUrl ();
        $this->param = $this->getParams ();
        $this->body = $this->getBody ();
    }
    /*********** ESTÁTICAS ***********/
    //Homologa la estructura de un URL
    final public static function castUrl (string $url) : string {
        return trim (preg_replace (["`^//`", "`^/{0,1}`", "`/$`", "`/\?`", "`^/\+`"], ["/+", "/", "", "?", "//"], trim ($url)));
    }

    //Remueve las variables enviadas por GET
    final public static function rmGets (string $url) : string {
        return preg_replace (self::GET_PATTERN, "", $url);
    }

    //Retorna una url con parámetros como una expresión regular
    final public static function getRegexUrl (string $url, bool $strict = true) : string {
        return "`^".preg_replace (["`\.`", self::PARAMS_PATTERN, "`/$`"], ["\.", "/([a-z0-9%_]+)", ""], self::rmGets ($url)).($strict ? "$`i" : "`i");
    }
    /*********************************/

    //Regresa un objeto con los valores enviados con formato url
    final private function getValuesUrl (string $input = null) : object {
        $values = [];

        preg_match_all ($this::VAL_PATTERN, ($input ?? $_SERVER["REQUEST_URI"]), $values);

        return (object) array_map (function ($val) {
            return trim(urldecode ($val));
        }, array_combine ($values[1], $values[2]));
    }

    //Retorna un objeto con los parámetros pasados por url
    final private function getParams () : object {
        $keys = [];
        $vals = [];
        //Nombre de parámetro
        preg_match_all ($this::PARAMS_PATTERN, $this->url, $keys);
        //Valor de parámetro
        preg_match_all ($this->getRegexUrl ($this->rmGets ($this->url)), $this->castUrl ($this->rmGets ($_SERVER["REQUEST_URI"])), $vals);
        array_shift ($vals);

        return (object) array_combine ($keys[1], array_map (function ($v) {
            return urldecode ($v[0]);
        }, $vals));
    }

    //Retorna un objeto con valores pasados por body
    final private function getBody () : object {
        return ((file_get_contents ("php://input", false, null, 0, 5) === "") || ($_SERVER["REQUEST_METHOD"] === "GET"))
            ? (object) $_POST
            : $this->getValuesUrl (file_get_contents ("php://input"));
    }
}

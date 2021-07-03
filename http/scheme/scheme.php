<?php
/*
 * FECHA: 2021/06/12
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: scheme.php
 * 
 * Descripción: Clase validadora de esquema.
*/

namespace http\scheme;

use http\request\REQUEST;
use HTTPException;
use Exception;

final class SCHEME
{
    private object $__req;

    public function __construct (REQUEST $req) {
        $this->__req = $req;
    }

    final public function test (array $scheme) : bool {
        $scheme["param"] = isset($scheme["param"]) ? $scheme["param"] : [];
        $scheme["get"] = isset($scheme["get"]) ? $scheme["get"] : [];
        $scheme["body"] = isset($scheme["body"]) ? $scheme["body"] : [];

        try {
            $this->iterate ($scheme["param"], $this->__req->param);
            $this->iterate ($scheme["get"], $this->__req->get);
            $this->iterate ($scheme["body"], $this->__req->body);
        } catch (Exception $e) {
            if ($e->getMessage() === "false") {
                return false;
            } else {
                throw new HTTPException (500, $e->getMessage());
            }
        }

        return true;
    }

    final private function iterate (array $scheme, object $req) : void {
        foreach ($scheme as $k => $s) {
            (string) $type = isset($s["type"]) ? $s["type"] : "";
            (bool) $validate = isset($s["validate"]) ? $s["validate"] : true;
            (bool) $require = (substr($k, -1) != "*") ? true : false;
            
            $k = str_replace ("*", "", $k);

            if ($require && !isset($req->$k)) {
                throw new Exception ("false");
            }
            
            if (isset($req->$k)) {
                if ($this->checkType ($type, $req->$k, $validate) == false) {
                    throw new Exception ("false");
                }
            }
        }
    }

    final private function checkType (string $type, string $value, $validate = true) : bool {
        $flag = false;

        switch ($type) {
            case "alpha":
                $flag = preg_match("`^[A-Z]+$`i", $value);
                break;
            case "number":
                $flag = preg_match("`^-?\d+(\.\d+)?$`", $value);
                break;
            case "alphanumeric":
                $flag = preg_match("`^[A-Z0-9]+$`i");
                break;
            case "date":
                $flag = preg_match("`^\d{4}-[0-1]\d-[0-3]\d$`", $value);
                break;
            case "time":
                $flag = preg_match("`^[0-2]\d:[0-5]\d(:[0-5]\d(\.\d+)?)?$`", $value);
                break;
            case "datetime":
                $flag = preg_match("`^\d{4}-[0-1]\d-[0-3]\d +[0-2]\d:[0-5]\d(:[0-5]\d(\.\d+)?)?$`", $value);
                break;
            case "":
                $flag = true;
                break;
            default:
                $flag = (preg_match($type, $value) == $validate) ? true : false;
                break;
        }
        
        return $flag;
    }
}
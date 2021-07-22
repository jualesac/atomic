<?php
/*
 * FECHA: 2021/07/14
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: schFile.php
 *
 * Descripción: Clase principal de carga de archivo
*/

namespace scheme;

require ("dataTypes.php");

use SplFixedArray;
use Exception;

abstract class SCHFile extends DATATYPES
{
    private $__file;

    protected array $__schema;
    protected int $__countColumns;
    protected array $__parameters;
    protected SplFixedArray $__fileIndex;

    protected function __construct (array $schema) {
        parent::__construct ();

        $this->__schema = $schema;
        $this->__countColumns = 0;
        $this->__fileIndex = new SplFixedArray (0);
    }

    final public function loadFile ($file, array $parameters = []) : void {
        $this->constructor ($parameters);
        $this->__file = $file;
        $this->strict = true;

        for ($n = 1; $n < $this->__parameters["line"]; $n++) {
            fgets ($this->__file);
        }

        if ($this->__parameters["mode"] && $this->__parameters["colname"]) {
            $this->columnsName ();
        }
    }

    private function constructor ($params) : void {
        (int) $this->__parameters["mode"] = (($params["mode"] ?? "delimited") == "delimited") ? 1 : 0;
        (int) $this->__parameters["line"] = $params["line"] ?? 1;
        (bool) $this->__parameters["colname"] = $params["colname"] ?? false;
        (string) $this->__parameters["delimiter"] = $params["delimiter"] ?? ",";
    }

    private function columnsName () : void {
        $line = $this->getLine ();

        if ($line === null) {
            throw new Exception ("Impossible to get headers");
        }

        foreach ($this->__schema as $name => $properties) {
            (bool) $require = (substr($name, -1) != "*") ? true : false;
            (string) $name = str_replace ("*", "", $name);
            
            $numberColumn = array_search ($name, $line);

            if (is_int($numberColumn)) { $this->__countColumns++; }

            if ($require && $numberColumn === false) {
                throw new Exception ("Unable to locate column: '{$name}'");
            }

            $this->__fileIndex[$this->upSize()] = $numberColumn;
        }
    }

    final protected function getLine () {
        if (feof($this->__file)) {
            return null;
        }

        $line = [];

        if ($this->__parameters["mode"]) {
            $line = array_map (function ($cell) {
                return $this->utf8 ($cell ?? "");
            }, ($this->__parameters["delimiter"] === ",")
                ? str_getcsv(fgets($this->__file))
                : explode ($this->__parameters["delimiter"], fgets($this->__file))
            );
        } else {
            $str = fgets ($this->__file);

            foreach ($this->__schema as $range => $a) {
                $r = explode ("|", $range);

                $line[] = $this->utf8 ( substr($str, $r[0], $r[1]) );
            }
        }

        return $line;
    }

    private function upSize () : int {
        $c = $this->__fileIndex->count ();

        $this->__fileIndex->setSize ($c + 1);

        return $c;
    }

    private function utf8 (string $text) : string {
        if ($text && (mb_detect_encoding($text, "UTF-8", true) !== "UTF-8")) {
            $text = utf8_encode ($text);
        }

        return trim ($text);
    }
}

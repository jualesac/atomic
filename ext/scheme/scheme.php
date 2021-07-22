<?php
/*
 * FECHA: 2021/07/14
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: schFile.php
 *
 * Descripción: Clase integradora de esquema de archivos y datos
*/

namespace scheme;

require ("schFile.php");

use Exception;

final class SCHEME extends SCHFile
{
    private bool $__isObject;

    public function __construct (array $schema) {
        parent::__construct ($schema);

        $this->__isObject = false;
    }

    final public function test (&$values) : bool {
        $this->__isObject = is_object ($values);
        $result = true;

        foreach ($this->__schema as $name => $properties) {
            (bool) $require = (substr($name, -1) != "*") ? true : false;
            (string) $name = str_replace ("*", "", $name);
            
            if ( !$require && ($this->__isObject ? !isset($values->$name) : !isset($values[$name])) ) {
                continue;
            }

            if ( $require && ($this->__isObject ? !isset($values->$name) : !isset($values[$name])) ) {
                return $this->boolStrict (false, "Schema structure not localized: {$name}");
            }

            $prop = $this->propertiesConstructor (is_array($properties) ? $properties : [ $properties ]);

            if ($this->__isObject) {
                $result = $this->checkType ($values->$name, $prop[0], $prop[1], $prop[2]);
            } else {
                $result = $this->checkType ($values[$name], $prop[0], $prop[1], $prop[2]);
            }

            if (!$result) { return false; }
        }

        return true;
    }

    final public function testLine () {
        $line = $this->getLine ();
        $newLine = [];

        if ($line === null) {
            return null;
        }

        if ($this->__parameters["mode"] && $this->__parameters["colname"] && count($line) < $this->__countColumns) {
            return $newLine;
        }

        $c = 0;

        foreach ($this->__schema as $properties) {
            $prop = $this->propertiesConstructor (is_array($properties) ? $properties : [ $properties ]);

            if ($this->__parameters["mode"]) {
                if ($this->__parameters["colname"] && ($this->__fileIndex[$c] === false)) {
                    $c++;
                    continue;
                }

                $value = $this->__parameters["colname"] ? $line[$this->__fileIndex[$c]] : $line[$c];
            } else {
                $value = $line[$c];
            }

            if (!$this->checkType($value, $prop[0], $prop[1], $prop[2])) {
                return [];
            }

            $newLine[] = $value;

            $c++;
        }

        return $newLine;
    }

    private function propertiesConstructor (array $args) : array {
        if (!isset($args[1])) {
            return [
                $args[0],
                true,
                function ($v) { return $v; }
            ];
        }

        if (is_bool($args[1])) {
            return [
                $args[0],
                $args[1],
                is_callable ($args[2] ?? 1) ? $args[2] : function ($v) { return $v; }
            ];
        }

        if (is_callable($args[1])) {
            return [
                $args[0],
                true,
                $args[1]
            ];
        }

        throw new Exception ("Incorrect schema structure");
    }
}

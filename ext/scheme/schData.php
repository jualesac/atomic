<?php
/*
 * FECHA: 2021/07/14
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: schData.php
 *
 * Descripción: Clase principal de cotejo de datos
*/

namespace scheme;

require ("dataTypes.php");

use Exception;

abstract class SCHData extends DATATYPES
{
    private bool $__isObject;

    protected array $__schema;

    protected function __construct (array $schema) {
        parent::__construct ();

        $this->__schema = $schema;
        $this->__isObject = false;
    }

    final public function test (array|object &$values) : bool {
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

    final protected function propertiesConstructor (array $args) : array {
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

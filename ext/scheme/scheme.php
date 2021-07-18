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
    public function __construct (array $schema) {
        parent::__construct ($schema);
    }

    final public function testLine () : array|null {
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
}
<?php
/*
 * FECHA: 2019/09/04
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: file.php
 *
 * Descripción: Manejador de archivos.
 * Reedición: 2020/01/14
*/

namespace file;

use Exception;

final class FILE
{
    private const URI = "/tmp/"; //No olvidar la diagonal final
    private $alias = [];
    public $archivos = [];
    private $default;

    function __destruct () {
        $alias;
        $archivo;

        foreach ($this->archivos as $alias => $archivo) {
            fclose ($archivo);
            unlink ($this->alias[$alias]["path"]);
        }
    }

    function __clone () {
        $this->alias = [];
        $this->archivos = [];
        $this->select = null;
    }

    final public function setFile ($alias, string $path = null) {
        $archivo;
        $modo = "w";
        $alia;
        $arr = [];
        $k;
        $a;
        $i;

        if (!is_array ($alias) && !is_object ($alias)) {
            if (array_key_exists ($alias, $this->archivos)) {
                throw new Exception ("500::No es posible cambiar el archivo con alias \"{$alias}\" porque se encuentra abierto.");
            }

            if ($path) {
                $archivo = realpath ($path);

                if ($archivo) {
                    $modo = "r";
                } else {
                    throw new Exception ("500::No fue posible localizar el archivo \"{$path}\"");
                }
            } else {
                $archivo = $this::URI.date ("YmdHis").$this->random (18);
            }
            //Incerción exitosa
            $this->alias[$alias] = [ "path" => $archivo, "modo" => $modo ];
        } else {
            foreach ($alias as $k => $alia) {
                if (is_array ($alia)) {
                    //Si es un archivo enviado por POST
                    if (array_key_exists ("name", $alia)) {
                        $this->setFile ($k, $alia["tmp_name"]);
                    } else {
                        //En caso de ser un array de archivos POST
                        $i = 0;

                        foreach ($alia as $a) {
                            $arr["{$k}_{$i}"] = $a;

                            $i++;
                        }

                        $this->setFile ($arr);
                    }
                } else {
                    if (is_int ($k)) {
                        $this->setFile ($alia);
                    } else {
                        $this->setFile ($k, $alia);
                    }
                }
            }
        }
    }

    final public function getFile (string $alias = null) {
        $alias = $alias ?? $this->default;

        $this->alias ($alias);

        if (!isset ($this->archivos[$alias])) {
            $this->open ($alias);
        }

        return $this->archivos[$alias];
    }

    final public function getAlias () {
        return array_keys ($this->alias);
    }

    final public function getPath (string $alias = null) {
        $alias = $alias ?? $this->default;

        $this->alias ($alias);

        return $this->alias[$alias]["path"];
    }

    final public function getLine (string $alias = null):string {
        $alias = $alias ?? $this->default;
        $linea;

        $this->alias ($alias);

        if (!isset ($this->archivos[$alias])) {
            $this->open ($alias);
        }

        if (!($linea = trim (fgets ($this->archivos[$alias])))) {
            throw new Exception ("500::Ocurrió un error al intentar leer el archivo");
        }

        return $linea;
    }

    final public function write (string $alias, string $mensaje = null) {
        if (!$mensaje) {
            $mensaje = $alias;
            $alias = null;
        }

        $alias = $alias ?? $this->default;

        if (!$alias) {
            throw new Exception ("500::Se debe especificar un alias o definir uno por default");
        }

        $this->alias ($alias);

        if (!isset ($this->archivos[$alias])) {
            $this->open ($alias);
        }

        if (!(fwrite ($this->archivos[$alias], $mensaje))) {
            throw new Exception ("500::Error al intentar escribir en el archivo");
        }
    }

    final public function endLine (string $alias = null): bool {
        $alias = $alias ?? $this->default;

        if (!$alias) {
            throw new Exception ("500::Se debe especificar un alias o definir uno por default");
        }

        $this->alias ($alias);

        if (!isset ($this->archivos[$alias])) {
            throw new Exception ("500::El archivo \"{$alias}\" no se encuentra abierto");
        }

        return feof ($this->archivos[$alias]);
    }

    final public function default (string $alias = null) {
        if ($alias) {
            $this->alias ($alias);
        }

        $this->default = $alias;
    }

    final public function open (string $alias = null, string $modo = null) {
        $alias = $alias ?? $this->default;

        $alia;
        $archivo;

        if ($alias) {
            $this->alias ($alias);

            if (!($this->archivos[$alias] = fopen ($this->alias[$alias]["path"], $modo ?? $this->alias[$alias]["modo"])) && !isset ($this->archivos[$alias])) {
                throw new Exception ("500::Error al abrir el archivo \"{$alias}\"");
            }
        } else {
            array_map (function ($alia) {
                $this->open ($alia);
            }, array_keys ($this->alias));
        }
    }

    final public function close (string $alias = null) {
        $alias = $alias ?? $this->default;

        $alia;
        $archivo;

        if ($alias) {
            $this->alias ($alias);

            if (!isset ($this->archivos[$alias])) {
                return;
            }

            fclose ($this->archivos[$alias]);

            unset ($this->archivos[$alias]);
        } else {
            array_map (function ($alia) {
                $this->close ($alia);
            }, array_keys ($this->archivos));
        }
    }

    final public function move (string $alias, string $path) {
        $this->alias ($alias);

        if (isset ($this->archivos[$alias])) {
            $this->close ($alias);
        }

        if (!file_exists ($this->alias[$alias]["path"])) {
            throw new Exception ("500::El archivo no pudo ser localizado");
        }

        if (!(rename ($this->alias[$alias]["path"], $path))) {
            throw new Exception ("500::Ocurrió un problema al intentar mover el archivo");
        }

        $this->alias[$alias]["path"] = realpath ($path);
    }

    private function alias (string $alias = null) {
        if (!$alias) {
            throw new Exception ("500::Se debe especificar un alias o definir uno por default");
        }

        if (!isset ($this->alias[$alias])) {
            throw new Exception ("500::No se pudo localizar el alias \"{$alias}\"");
        }
    }

    private function random (int $cantidad) {
        $caracteres = [1, "A", 2, "B", 3, "C", 4, "D", 5, "E", 6, "F", 7, "G", 8, "H", 9, "I", 0, "J"];
        $clave = "";
        $i;

        for ($i = 0; $i < $cantidad; $i++) {
            $clave .= $caracteres[rand(0, 19)];
        }

        return $clave;
    }
}

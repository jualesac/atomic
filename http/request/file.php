<?php
/*
 * FECHA: 2021/06/08
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: file.php
 *
 * Descripción: Clase validadora de archivos cargados
*/

namespace http\request;

use Exception;

abstract class FILE
{
    private const FILE_SIZE = 67108864;
    protected object $file;

    function __construct () {
        $this->file = $this->getFiles ();
    }

    private function getFiles () : object {
        $files = $_FILES;
        $arrFiles = [];
        $file;
        $f; $k; $i;

        foreach ($files as $k => $file) {
            if (is_array ($file["name"])) {
                foreach ($file["name"] as $i => $f) {
                    $arrFiles[$k][] = [
                        "name" => $f,
                        "type" => $file["type"][$i],
                        "tmp_name" => $file["tmp_name"][$i],
                        "error" => $file["error"][$i],
                        "size" => $file["size"][$i]
                    ];
                }

                continue;
            }

            $arrFiles[$k] = $file;
        }

        return $this->checkFiles ($arrFiles);
    }

    private function checkFiles (array $files) : object {
        array_map (function ($file) {
            if (isset ($file["name"])) {
                //Comprobaciones de archivo
                if (!is_uploaded_file ($file["tmp_name"])) {
                    throw new Exception ("205::El archivo \"{$file["name"]}\" no fue subido por un protocolo válido.");
                }

                if ($file["size"] > self::FILE_SIZE) {
                    throw new Exception ("205::El archivo \"{$file["name"]}\" supera el tamaño permitido.");
                }

                return;
            }

            $this->checkFiles ($file);
        }, $files);

        return (object) $files;
    }
}

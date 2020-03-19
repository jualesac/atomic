<?php
/*
 * FECHA: 2019/09/12
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: scheme.php
 *
 * Descripción: Manejador de esquemas para archivos y arreglos.
 *
 * Reedición - 2020/01/07: Soporte para archivos de ancho fijo.
*/

namespace scheme;

require ("scheme_types.php");

use Exception;
use http\HTTP;

final class SCHEME extends SCHEME_TYPES
{
    private $scheme;
    private $archivo;
    private $modo;
    private $delimitador;
    private $colname;
    private $columnas = [];
    private static $stc_scheme;

    function __construct ($archivo, array $scheme) {
        $this->archivo = $archivo;
        $this->scheme = $scheme;
        $this->colname = $scheme["head"]["colname"] ?? false;

        switch ($scheme["head"]["mode"] ?? "delimited") {
            case "delimited":
                $this->modo = 1;
                $this->delimitador = $scheme["head"]["delimiter"] ?? null;
            break;

            case "fixed":
                $this->modo = 0;
            break;

            default:
                throw new Exception ("206::No fue posible reconocer el modo especificado.");
            break;
        }

        $this->columnas ();
    }

    final public static function scheme (array $esquema) {
        $scheme;
        $replace;

        foreach ($esquema as $scheme) {
            $replace = $scheme["replace"] ?? null;

            if (!((($scheme["find"] ?? null) && isset ($replace)) === (($scheme["find"] ?? null) || isset ($replace)))) {
                throw new Exception ("206::Un esquema find requiere un replace");
            }
        }

        self::$stc_scheme = $esquema;
    }

    final public static function get (array $arreglo) {
        $scheme;
        $find;
        $replace;
        $k;

        if (count ($arreglo) != count (self::$stc_scheme)) {
            throw new Exception ("500::El número de datos pasados no corresponde con el esquema configurado");
        }

        foreach (self::$stc_scheme as $k => $scheme) {
            $find = $scheme["find"] ?? null;
            $replace = $scheme["replace"] ?? null;

            if ($find) {
                self::find ($arreglo[$k], $find, $replace);
            }

            if ($scheme["type"] !== "") {
                self::type ($arreglo[$k], $scheme["type"]);
            }
        }

        return $arreglo;
    }

    private function columnas () {
        $linea = [];
        $scheme;
        $replace;
        $i;

        $lCabecera = $this->scheme["head"]["line"] ?? 0;

        for ($i = 0; $i < $lCabecera; $i++) {
            $linea = $this->line ();
        }

        foreach ($this->scheme["columns"] as $scheme) {
            $replace = $scheme["replace"] ?? null;

            if ($this->modo && $this->colname) {
                $i = $this->columnas[] = array_search ($scheme["name"], $linea);

                if ($i === false) {
                    throw new Exception ("206::No se fue posible localizar la columna \"{$scheme["name"]}\"");
                }
            }

            if (!((($scheme["find"] ?? null) && isset ($replace)) === (($scheme["find"] ?? null) || isset ($replace)))) {
                throw new Exception ("206::Un esquema find de columna requiere un esquema replace");
            }
        }
    }

    final public function getLine (bool $lineaTotal = false) {
        $scheme;
        $linea;
        $lineaParcial = [];
        $columna;
        $find;
        $replace;
        $k;

        if (feof ($this->archivo)) {
            return false;
        }

        $linea = $this->line ();

        if ($linea == "" || (count ($linea) < count ($this->scheme["columns"]))) {
            return [""];
        }

        foreach ($this->scheme["columns"] as $k => $scheme) {
            $columna = (count ($this->columnas) > 0) ? $this->columnas[$k] : $k;
            $find = $scheme["find"] ?? null;
            $replace = $scheme["replace"] ?? null;

            //Reemplazo
            if ($find) {
                $this::find ($linea[$columna], $find, $replace);
            }
            //Tipo
            if ($scheme["type"] !== "") {
                $this::type ($linea[$columna], $scheme["type"]);
            }

            if ($this->modo && !$lineaTotal) {
                $lineaParcial[] = $linea[$columna];
            }
        }

        return ($this->modo) ? ($lineaTotal ? $linea : $lineaParcial) : $linea;
    }

    private static function type (&$dato, $tipo) {
        switch ($tipo) {
            case "alphanumeric":
                $dato = self::alphaNumeric ($dato);
            break;

            case "number":
                $dato = self::number ($dato);
            break;

            case "date":
                $dato = self::date ($dato);
            break;

            case "time":
                $dato = self::time ($dato);
            break;

            case "datetime":
                $dato = self::dateTime ($dato);
            break;

            default:
                $dato = self::pattern ($dato, $tipo);
            break;
        }
    }

    private function line () {
        $linea;
        $string;
        $scheme;

        if ($this->modo) {
            $linea = array_map (function ($celda) {
                    return trim (HTTP::utf8 ($celda));
                }, ($this->delimitador) ?
                    explode ($this->delimitador, fgets ($this->archivo)) :
                    str_getcsv (fgets ($this->archivo))
            );
        } else {
            $string = fgets ($this->archivo);

            foreach ($this->scheme["columns"] as $scheme) {
                $linea[] = trim (HTTP::utf8 (substr ($string, $scheme["start"], $scheme["size"])));
            }
        }

        return $linea;
    }

    private static function find (&$dato, $find, $replace) {
        if (!($dato != "" && ($dato = @trim (preg_replace ($find, $replace, $dato))))) {
            throw new Exception ("500::Ocurrió problema al reemplazar el dato");
        }
    }
}

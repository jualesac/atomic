<?php
/*
 * FECHA: 2020/01/06
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: scheme_types.php
 *
 * Descripción: Clase contenedora de métodos de casteo,
 *              la original se realizó en 2019/09/12.
*/

namespace scheme;

use Exception;

abstract class SCHEME_TYPES
{
    private const MONTHS = [
        "ene" => "01",
        "feb" => "02",
        "mar" => "03",
        "abr" => "04",
        "may" => "05",
        "jun" => "06",
        "jul" => "07",
        "ago" => "08",
        "sep" => "09",
        "oct" => "10",
        "nov" => "11",
        "dic" => "12",
        "jan" => "01",
        "apr" => "04",
        "aug" => "08",
        "dec" => "12"
    ];

    protected static function alphaNumeric ($dato) {
        if ($dato === "") {
            return "";
        }

        if (preg_match ("`[^a-z0-9_]`i", $dato)) {
            throw new Exception ("206::El campo no corresponde a un tipo alfanumérico");
        }

        return $dato;
    }

    protected static function number ($dato) {
        $valorRetorno;

        if ($dato === "") {
            return "0";
        }

        $dato = str_replace (["$", "%", ",", "'"], "", $dato);

        if (!preg_match ("`^(?:-|\d)*\.?\d*$`", $dato, $valorRetorno)) {
            throw new Exception ("206::El campo no corresponde a un tipo numérico");
        }

        $dato = $valorRetorno[0];

        $dato = preg_replace ("`^((0+\.)|(\.0+)|\.|(-\.0*)|(-0+\.))$`", "0", $dato);

        if (preg_match ("`\.\d*$`", $dato)) {
            $dato = preg_replace (["`0+$`", "`\.$`"], [""], $dato);
        }

        $dato = preg_replace (["`^0+`", "`^\.`", "`^-0+`", "`^-\.`", "`^-$`"], ["", "0.", "-", "-0.", "0"], $dato);

        if ($dato == "") {
            $dato = self::number ($dato);
        }

        return $dato;
    }

    protected static function date ($dato) {
        $valorRetorno;
        $ano;
        $mes;
        $dia;

        if ($dato === "") {
            return "0000-00-00";
        }

        if (preg_match ("`(ene)|(feb)|(mar)|(abr)|(may)|(jun)|(jul)|(ago)|(sep)|(oct)|(nov)|(dic)|(jan)|(apr)|(aug)|(dec)`i", $dato, $valorRetorno)) {
            $dato = str_replace ($valorRetorno[0], self::MONTHS[$valorRetorno[0]], $dato);
        }

        if (!preg_match ("`^(\d{4}\D[0-1]\d\D[0-3]\d)|([0-3]\d\D[0-1]\d\D\d{4})`", $dato, $valorRetorno)) {
            throw new Exception ("206::El campo no corresponde a un tipo fecha");
        }

        $dato = $valorRetorno[0];

        preg_match ("`\d{4}`", $dato, $valorRetorno);
        $ano = $valorRetorno[0];
        preg_match ("`\D[0-1]\d\D`", $dato, $valorRetorno);
        $mes = preg_replace ("`\D`", "", $valorRetorno[0]);
        preg_match ("`(\D[0-3]\d$)|(^[0-3]\d\D)`", $dato, $valorRetorno);
        $dia = preg_replace ("`\D`", "", $valorRetorno[0]);

        return ("{$ano}-{$mes}-{$dia}");
    }

    protected static function time ($dato) {
        $valorRetorno;
        $hor;
        $min;
        $seg;
        $pm;
        $am;

        if ($dato === "") {
            return "00:00:00";
        }

        preg_match ("` *p.{0,4}m.{0,4} *`i", $dato, $pm);
        preg_match ("` *a.{0,4}m.{0,4} *`i", $dato, $am);

        $dato = preg_replace ("` *(p|a).{0,4}m.{0,4} *`i", "", $dato);

        if (!preg_match ("`(?:^|\D)[0-2]\d\D[0-5]\d\D[0-5]\d(?:\.\d+)?$`", $dato, $valorRetorno)) {
            throw new Exception ("206::El campo no corresponde a un tipo tiempo");
        }

        $dato = $valorRetorno[0];

        preg_match ("`(?:^|\D)([0-2]\d)`", $dato, $valorRetorno);
        $hor = $valorRetorno[1];
        preg_match ("`(?:\d\D)([0-5]\d)\D[^\.]`", $dato, $valorRetorno);
        $min = $valorRetorno[1];
        preg_match ("`.([0-5]\d(?:\.\d+)*)$`", $dato, $valorRetorno);
        $seg = $valorRetorno[1];

        if ($hor > 23) {
            throw new Exception ("206::Incongruencia en la hora");
        }

        if (count ($pm) && $hor != "12") {
            $hor = $hor + 12;
        }

        if (count ($am) && $hor == "12") {
            $hor = "00";
        }

        if ($seg >= 10) {
            $seg = self::number ($seg);
        }

        return ("{$hor}:{$min}:{$seg}");
    }

    protected static function dateTime ($dato) {
        $valorRetorno;
        $fecha;
        $tiempo;

        if ($dato === "") {
            return "0000-00-00 00:00:00";
        }

        if (preg_match ("`(ene)|(feb)|(mar)|(abr)|(may)|(jun)|(jul)|(ago)|(sep)|(oct)|(nov)|(dic)|(jan)|(apr)|(aug)|(dec)`i", $dato, $valorRetorno)) {
            $dato = str_replace ($valorRetorno[0], self::MONTHS[$valorRetorno[0]], $dato);
        }

        if (!preg_match ("`^(\d{4}\D[0-1]\d\D[0-3]\d)|(^[0-3]\d\D[0-1]\d\D\d{4})`", $dato)) {
            $dato = "0000-00-00 {$dato}";
        }

        if (!preg_match ("`(?:^|\D)[0-2]\d\D[0-5]\d\D[0-5]\d(?:\.\d+)?(?: *(a|p).{0,4}m.{0,4} *)?$`i", $dato)) {
            $dato .= " 00:00:00";
        }

        if (!preg_match ("`^(?:(?:\d{4}\D[0-1]\d\D[0-3]\d)|(?:[0-3]\d\D[0-1]\d\D\d{4})).[0-2]\d\D[0-5]\d\D[0-5]\d(?:\.\d+)?(?: *(?:a|p).{0,4}m.{0,4} *)?$`i", $dato, $valorRetorno)) {
            throw new Exception ("206::El campo no corresponde a un tipo dateTime");
        }

        $dato = $valorRetorno[0];
        $fecha = self::date ($dato);
        $tiempo = self::time ($dato);

        return ("{$fecha} {$tiempo}");
    }

    protected static function pattern ($dato = "", $patron = "``") {
        if ($patron === "" || $dato === "") {
            return $dato;
        }

        if (@!preg_match ($patron, $dato)) {
            throw new Exception ("206::El campo no contiene la estructura correcta o el esquema type no es una expresión regular");
        }

        return $dato;
    }
}

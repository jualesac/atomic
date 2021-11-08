<?php
/*
 * FECHA: 2021/07/16
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: dataTypes.php
 *
 * Descripción: Clase contenedora de tipos
*/

namespace scheme;

use Exception;

abstract class DATATYPES
{
    public bool $strict;

    protected function __construct () {
        $this->strict = true;
    }

    final protected function checkType (string &$value, string $type, bool $validate, callable $callback) : bool {
        $flag = true;
        
        $value = ($callback) ($value);

        switch ($type) {
            case "alpha":
                $flag = preg_match ("`^[a-z]+$`i", $value);
                break;
            case "number":
                $flag = $this->number ($value);
                break;
            case "alphanumeric":
                $flag = preg_match ("`^[a-z0-9]+$`i", $value);
                break;
            case "date":
                $flag = $this->date ($value);
                break;
            case "time":
                $flag = $this->time ($value);
                break;
            case "datetime":
                $flag = $this->datetime ($value);
                break;
            case "":
                $flag = true;
                break;
            default:
                $flag = (preg_match($type, $value) == $validate) ? true : false;
                break;
        }

        return $this->boolStrict ($flag, "Impossible to cast or the value '{$value}' does not correspond to the type/structure '{$type}'");
    }

    final protected function boolStrict (bool $flag, string $message) : bool {
        if (!$flag && $this->strict) {
            throw new Exception ($message);
        }

        return $flag;
    }

    private function number (string &$value) : bool {
        $value = preg_replace ([ "`[^0-9\-.]`", "`^-0+`", "`^0+`", "`^-\.`", "`^\.`", "`\.$`", "`^$`" ], [ "", "-", "", "-0.", "0.", ".00", "0"  ], $value);

        return preg_match ("`^-?\d+(\.\d+)?$`", $value);
    }

    private function date (string &$date) : bool {
        if ($date == "") {
            $date = "0000-00-00";
            return true;
        }

        $date = preg_replace (
            [ "`ene|jan`i", "`feb`i", "`mar`i", "`abr|apr`i", "`may`i", "`jun`i", "`jul`i", "`ago|aug`i", "`sep`i", "`oct`i", "`nov`i", "`dic|dec`i", "`[^0-9]+`i" ],
            [ "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "-" ],
            $date
        );

        if (
            preg_match ("`\d{4}`", $date, $year)
            && preg_match ("`-\d{2}-`", $date, $month)
            && preg_match ("`(?:^\d{2})-|-(?:\d{2}$)`", $date, $day)
        ) {
            $year = $year[0];
            $month = str_replace ("-", "", $month[0]);
            $day = str_replace ("-", "", $day[0]);

            $date = "{$year}-{$month}-{$day}";
        }

        return preg_match ("`^((\d{4}-(0[1-9]|1[0-2])-(0[1-9]|1\d|2\d|3[01]))|0000-00-00)$`", $date);
    }

    private function time (string &$time) : bool {
        if ($time == "") {
            $time = "00:00:00";
            return true;
        }

        $am = false;
        $pm = false;

        if (preg_match("`a[^am]*m.*`i", $time)) {
            $am = true;
        }

        if (preg_match("`p[^pm]*m.*`i", $time)) {
            $pm = true;
        }

        $time = trim (preg_replace("`(a[^am]*m.*)|(p[^pm]*m.*)`i", "", $time));

        $decimals = explode (".", $time);

        if (count($decimals) > 2) { return false; }

        $time = preg_replace ([ "`a[^am]*m.*`i", "`p[^pm]*m.*`i", "`[^0-9:\-/]`", "`[:\-/]+`" ], [ "", "", "", ":" ], $decimals[0]);
        $time = $time.(isset($decimals[1]) ? ".".(int)$decimals[1] : "");

        $timeExplode = explode (":", $time);

        if ($pm && $timeExplode[0] != "12") {
            $timeExplode[0] = $timeExplode[0] + 12;
        }

        if ($am && $timeExplode[0] == "12") {
            $timeExplode[0] = "00";
        }

        $time = implode (":", $timeExplode);

        return preg_match ("`^([01]\d|2[0-3]):([0-5]\d)((:[0-5]\d)?(\.\d+)?)?$`", $time);
    }

    private function datetime (string &$datetime) : bool {
        $datetime = preg_replace ([ "` *a[ .]*m.*`i", "` *p[ .]*m.*`i", "` +`" ], [ "AM", "PM", " " ], $datetime);

        if (preg_match("`^\d{2}:\d{2}`", $datetime)) {
            $datetime = " " + $datetime;
        } else {
            $datetime .= " ";
        }

        $datetimeExplode = explode (" ", trim($datetime));

        if (count($datetimeExplode) !== 2) {
            return false;
        }

        if ($this->date ($datetimeExplode[0]) && $this->time ($datetimeExplode[1])) {
            $datetime = implode (" ", $datetimeExplode);
            return true;
        } else {
            return false;
        }
    }
}

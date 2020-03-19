<?php
/*
 * FECHA: 2018/12/26
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: db.php
 *
 * Descripción: Contiene todos los métodos que interactúan con la base de datos.
 *
 * Actualización: 2019/07/24 - Adaptación para funcionar con la clase HTTP.
*/

namespace db;

require (__DIR__."/../config/db.php");

use PDO;
use PDOException;
use Exception;

final class DB extends CONFIG
{
    private $conexion;
    private $consulta;

    final private function conexion ($host = null, $db = null, $port = null, $user = null, $pass = null) {
        if (!is_object ($this->conexion)) {
            $host = $host ?? $this::HOST;
            $db = $db ?? $this::DB;
            $port = $port ?? $this::PORT;
            $user = $user ?? $this::USER;
            $pass = $pass ?? $this::PASS;

            try {
                $this->conexion = new PDO ("mysql:host={$host};dbname={$db};port={$port}", $user, $pass);
                $this->conexion->exec ("SET CHARACTER SET utf8mb4");
                $this->conexion->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new Exception ("500::".$e->getMessage ());
            }
        }
    }

    function __clone () {
        $this->conexion = null;
    }

    final public function query ($query, $buffer = TRUE) {
        $this->conexion ();

        try {
            $this->consulta = $this->conexion->prepare ($query, array (PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => $buffer));
            $this->consulta->execute ();
        } catch (PDOException $e) {
            throw new Exception ("404::".$e->getMessage ());
        }

        return $this->consulta;
    }

    final public function startTransaction () {
        $this->conexion->beginTransaction ();
    }

    final public function rollback () {
        $this->conexion->rollBack ();
    }

    final public function commit () {
        $this->conexion->commit ();
    }

    final public function loadData (String $archivo, String $tabla, String $separador) {
        if (preg_match ("` `", trim ($archivo).trim ($tabla).trim ($separador))) {
            throw new Exception ("500::Carga fallida.");
        }

        $query = "LOAD DATA INFILE \"".str_replace ("\\", "/", $archivo)."\" "
            . "INTO TABLE `".$tabla."` "
            . "FIELDS TERMINATED BY \"".$separador."\" "
            . "LINES TERMINATED BY \"\\n\"";

        $this->query ($query);
    }

    final public function conect ($host, $db, $user, $pass, $port) {
        if ($this->conexion === null) {
            $this->conexion ($host, $db, $port, $user, $pass);
        } else {
            throw new Exception ("500::Conexión activa, imposible realizar otra.");
        }
    }
}

<?php
/*
 * FECHA: 2021/06/28
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: db.php
 *
 * Descripción: Contiene todos los métodos que interactúan con la base de datos.
*/

namespace atomic\db;

require (__DIR__."/../config/db.php");

use PDO;
use PDOStatement;
use PDOException;
use http\HTTPException;

final class DB extends CONFIG
{
    private $__connection;
    private $__query;

    public function __construct (string $host = null, string $db = null, string $user = null, string $pass = null, int $port = null) {
        $this->host = $host ?? $this->host;
        $this->db = $db ?? $this->db;
        $this->user = $user ?? $this->user;
        $this->pass = $pass ?? $this->pass;
        $this->port = $port ?? $this->port;
    }

    private function connect () : void {
        if (is_object($this->__connection)) { return; }

        try {
            $this->__connection = new PDO ("mysql:host={$this->host};dbname={$this->db};port={$this->port}", $this->user, $this->pass);
            $this->__connection->exec ("SET CHARACTER SET utf8");
            $this->__connection->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new HTTPException (500, $e->getMessage());
        }
    }

    final public function query (string $query, bool $buffer = true) : PDOStatement {
        $this->connect ();

        try {
            $this->__query = $this->__connection->prepare ($query, [PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => $buffer]);
            $this->__query->execute ();

            return $this->__query;

        } catch (PDOException $e) {
            throw new HTTPException (500, $e->getMessage());
        }
    }

    final public function startTransaction () : void {
        $this->__connection->beginTransaction ();
    }

    final public function rollback () : void {
        $this->__connection->rollback ();
    }

    final public function commit () : void {
        $this->__connection->commit ();
    }

    final public function loadData (string $file, string $table, string $separator) : void {
        if (preg_match("` `", trim($file).trim($table).trim($separator))) {
            throw new HTTPException (500, "Error loadData");
        }

        $file = str_replace ("\\", "/", $file);

        $query = <<<QUERY
            LOAD DATA INFILE "{$file}"
            INTO TABLE `{$table}`
            FIELDS TERMINATED BY "{$separator}"
            LINES TERMINATED BY "\\n"
QUERY;
        $this->query ($query);
    }
}
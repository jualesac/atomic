<?php
/*
 * FECHA: 2022/07/09
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: cfgsftp.php
 *
 * Descripción: Configuración de conexión SFTP
*/

namespace sftp;

use Exception;

abstract class CFGSFTP
{
    protected string $_server;
    protected string $_user;
    protected string $_password;
    protected string $_pubKey;
    protected string $_privKey;
    protected int $_port;

    protected function __construct (array $config) {
        $this->_server = trim ($config["server"] ?? ($config[0] ?? ""));
        $this->_user = trim ($config["user"] ?? ($config[1] ?? ""));
        $this->_password = trim ($config["password"] ?? ($config[2] ?? ""));
        $this->_pubKey = trim ($config["pubKey"] ?? "");
        $this->_privKey = trim ($config["privKey"] ?? "");
        $this->_port = $config["port"] ?? 22;

        if ($this->_server == "" || $this->_user == "") {
            throw new Exception ("Server name and username are required.");
        }

        if (isset($config[3])) {
            if (is_int($config[3])) {
                $this->_port = $config[3];
            } else {
                $this->_pubKey = trim ($config[3] ?? "");
                $this->_privKey = trim ($config[4] ?? "");
                $this->_port = $config[5] ?? 22;
            }
        }

        $this->keyExists ($this->_pubKey);
        $this->keyExists ($this->_privKey);
    }

    private function keyExists (string $path) : void {
        if ($path == "") { return; }

        if (!file_exists($path)) {
            throw new Exception ("The '{$path}' key could not be located.");
        }
    }
}

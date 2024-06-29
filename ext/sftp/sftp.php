<?php
/*
 * FECHA: 2022/07/09
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: sftp.php
 * 
 * Descripción: Clase para realizar conexiones sftp.
*/

namespace sftp;

require ("cfgsftp.php");

use Exception;

final class SFTP extends CFGSFTP
{
    private $_connection;
    private string $_url;
    private $_dir;

    public array $connectDir = [];
    public bool $fileInformation = false;

    public function __construct (array $config) {
        parent::__construct ($config);

        $this->_url = "";

        $this->connect ();

        $this->cd ("/");
    }

    public function __destruct () {
        $this->disconnect ();
    }

    final public function cd (string $path) : void {
        if (!preg_match("`^ssh2`", $this->_url)) {
            $this->castPath ($path);

            $this->_url = "ssh2.sftp://{$this->_connection}{$path}";
            return;
        }

        $this->castPath ($this->_url);
        $this->castFile ($path);

        $this->_url = preg_replace ("`^/`", "", $this->_url."/{$path}");
    }

    final public function disconnect () : void {
        if (!isset($this->_connection)) { return; }

        $this->closeDir ();

        ssh2_disconnect ($this->_connection);
        unset ($this->_connection);
    }

    final public function exploreDir (string $path = "") : array {
        $this->castFile ($path);

        $dir = opendir ("{$this->_url}/{$path}");
        $this->contentDir = [];

        while ($item = readdir($dir)) {
            if ($this->fileInformation) {
                $this->contentDir[] = $this->getFileInformation ($item);
            } else {
                $this->contentDir[] = $item;
            }
        }

        closedir ($dir);

        return $this->contentDir;
    }

    final public function openDir (string $path = "") : void {
        $this->cd ($path);

        $this->closeDir ();

        $this->_dir = opendir ($this->_url);
        $this->contentDir = [];
    }

    final public function getItem () : array {
        if (!isset($this->_dir)) { return []; }

        $item = readdir ($this->_dir);

        if ($item == false) {
            $this->closeDir ();

            return [];
        }

        return ($this->fileInformation) ? $this->getFileInformation ($item) : [ "name" => $item, 0 => $item ];
    }

    final public function receive (string $remoteFile, string $localFile) : void {
        $this->castFile ($remoteFile);

        $size = @filesize ("{$this->_url}/{$remoteFile}");
        $stream = @fopen ("{$this->_url}/{$remoteFile}", "r");

        if (!$stream) {
            throw new Exception ("Remote file not located.");
        }

        file_put_contents ($localFile, fread($stream, $size));
        fclose ($stream);
    }

    final public function send (string $localFile, string $remoteFile) : void {
        $this->castFile ($remoteFile);

        if (!file_exists($localFile)) {
            throw new Exception ("Local file could not be located.");
        }

        $stream = @fopen ("{$this->_url}/{$remoteFile}", "w");

        if (!$stream) {
            throw new Exception ("Problem sending the file.");
        }

        fwrite ($stream, file_get_contents($localFile));
        fclose ($stream);
    }

    final public function rm (string $file) : void {
        $this->castFile ($file);

        if (filetype("{$this->_url}/{$file}") == "dir") {
            rmdir ("{$this->_url}/{$file}");
        } else {
            unlink ("{$this->_url}/{$file}");
        }
    }

    final public function pwd () : string {
        return preg_replace ("`^ssh2.sftp://Resource id #\d+`", "", $this->_url);
    }

    private function closeDir () : void {
        if (!isset($this->_dir)) { return; }

        closedir ($this->_dir);
        unset ($this->_dir);
    }

    private function getFileInformation (string $item) : array {
        $type = @filetype ("{$this->_url}/{$item}");
        $size = @filesize ("{$this->_url}/{$item}");

        if (!($type && $size)) {
            throw new Exception ("Problem getting file information.");
        }

        return [
            "type" => $type,
            "name" => $item,
            "size" => $size,
            0 => $type,
            1 => $item,
            2 => $size
        ];
    }

    private function connect () : void {
        $conn = @ssh2_connect ($this->_server, $this->_port);

        if ($conn === false) {
            throw new Exception ("Failed to try to connect.");
        }

        if ($this->_pubKey) {
            ssh2_auth_pubkey_file ($conn, $this->_user, $this->_pubKey, $this->_privKey);
        } else {
            ssh2_auth_password ($conn, $this->_user, $this->_password);
        }

        $this->_connection = ssh2_sftp ($conn);
    }

    private function castPath (string &$path) : void {
        $path = preg_replace ([ "`^$`", "`^(?!/)`", "`(?<!^)/$`" ], [ "/", "/", "" ], trim($path));
    }

    private function castFile (string &$file) : void {
        $file = preg_replace ([ "`^/`", "`/$`" ], "", trim($file));
    }
}

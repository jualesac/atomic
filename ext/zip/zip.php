<?php
/*
 * FECHA: 2023/07/04
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: zip.php
 * 
 * Descripción: Clase manejadora de zip.
*/

namespace zip;

use ZipArchive;
use Exception;

final class ZIP
{
    private string $_path;
    private ZipArchive $_zip;

    public string $timestamp;
    public string $tmpName;

    public bool $rmOriginalFile; //Eliminar los archivos originales luego de insertarlos al zip
    public bool $useTimestamp;
    public string $name;

    public function __construct () {
        $this->_path = __DIR__."/tmp";
        $this->timestamp = date ("Ymd_His");
        $this->tmpName = $this->createNameFolder ();

        $this->useTimestamp = true;
        $this->rmOriginalFile = false;

        $this->createZip ();
    }

    public function __destruct () {
        if ($this->_zip->filename != "") {
            $this->_zip->close ();
        }

        if (file_exists($this->_path."/".$this->tmpName)) {
            unlink ($this->_path."/".$this->tmpName);
        }
    }

    final public function setFile (string $name, string $file) : void {
        if (!file_exists($file)) {
            return;
        }

        $this->_zip->addFromString ($name, file_get_contents(realpath($file)));

        if ($this->rmOriginalFile) {
            unlink (realpath($file));
        }
    }

    final public function move (string $file) : void {
        if ($this->_zip->filename == "") {
            return;
        }

        $this->name = preg_replace ("`\.zip$`", "", trim($file)).($this->useTimestamp ? "_{$this->timestamp}" : "").".zip";

        $this->_zip->close ();

        if (!rename($this->_path."/".$this->tmpName, $this->name)) {
            throw new Exception ("Error moving file");
        }
    }

    private function createZip () : void {
        $this->_zip = new ZipArchive;
        $this->_zip->open ($this->_path."/".$this->tmpName, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $this->_zip->addFromString ("README", $this->timestamp);
    }

    private function createNameFolder () : string {
        $characters = [1, "A", 2, "B", 3, "C", 4, "D", 5, "E", 6, "F", 7, "G", 8, "H", 9, "I", 0, "J"];
        $name = "";

        for ($i = 0; $i < 20; $i++) {
            $name .= $characters[rand(0, 19)];
        }

        return $name;
    }
}

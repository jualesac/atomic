<?php
/*
 * FECHA: 2021/07/12
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: setFile.php
 * 
 * Descripción: Carga de archivos
*/

namespace file;

use SplFixedArray;
use Exception;

abstract class SetFile
{
    private const URI = __DIR__."/tmp/";

    protected SplFixedArray $__alias;
    protected SplFixedArray $__files;
    
    public bool $persistent;

    protected function __construct (array $args) {
        if (count($args) == 0) { throw new Exception ("An argument was expected"); }

        if (!isset($this->__alias)) {
            $this->__alias = new SplFixedArray (0);
            $this->__files = new SplFixedArray (0);
            $this->persistent = false;
        }

        if (is_string($args[0])) {
            $this->construct0 ($args[0], ($args[1] ?? null));
        }
        
        if (is_array($args[0])) {
            $this->construct1 ($args[0]);
        }

        if (is_object($args[0])) {
            $this->construct2 ($args[0]);
        }
    }
    
    public function __destruct () {
        if ($this->persistent) { return; }

        foreach ($this->__files as $n => $file) {
            if (is_null($file)) { continue; }

            fclose ($file);
            unlink ($this->__alias[$n][1]);
        }
    }

    private function construct0 (string $alias, string $path = null) : void {
        if (self::aliasExists($alias) > -1) { throw new Exception ("Duplicate aliases"); }

        $p = $path;
        $mode = "w";

        if ($path) {
            $path = realpath ($path);

            if (!$path) {
                throw new Exception ("Could not locate the file: '{$alias}'");
            }

            $mode = "r";
        } else {
            $path = $this->createName ();
        }

        $this->__alias[$this->sizeUp($this->__alias)] = [
            trim($alias),
            $path,
            $mode
        ];
        $this->sizeUp ($this->__files);
    }

    private function construct1 (array $files) : void {
        foreach ($files as $alias => $path) {
            $path = ($path === "") ? null : $path;

            $this->construct0 ($alias, $path);
        }
    }

    private function construct2 (object $files) : void {
        foreach ($files as $alias => $path) {
            if (isset($path["tmp_name"])) {
                $this->construct0 ($alias, ($path["tmp_name"] ?? null));
            } else {
                $f = 0;

                foreach ($path as $file) {
                    $this->construct0 ("{$alias}_".$f++, ($file["tmp_name"] ?? null));
                }
            }
        }
    }

    protected function aliasExists (string $alias) : int {
        $alias = trim ($alias);

        foreach ($this->__alias as $k => $a) {
            if ($a[0] == $alias) { return $k; }
        }

        return -1;
    }

    private function createName () : string {
        $characters = [1, "A", 2, "B", 3, "C", 4, "D", 5, "E", 6, "F", 7, "G", 8, "H", 9, "I", 0, "J"];
        $name = "";

        for ($i = 0; $i < 20; $i++) {
            $name .= $characters[rand(0, 19)];
        }

        return self::URI.date("YmdHis").$name;
    }

    private function sizeUp (SplFixedArray $splArray) : int {
        $k = $splArray->count ();

        $splArray->setSize ($k + 1);

        return $k;
    }
}

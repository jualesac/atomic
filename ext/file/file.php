<?php
/*
 * FECHA: 2021/07/12
 * AUTOR: Julio Alejandro Santos Corona
 * CORREO: jualesac@yahoo.com
 * TÍTULO: file.php
 * 
 * Descripción: Manejador de archivos.
*/

namespace file;

require ("setFile.php");

use Exception;

final class FILE extends SetFile
{
    private int $__default;
    private $__currentFile;
    private $__currentFileMode;

    public function __construct (...$args) {
        parent::__construct ($args);
    }

    final public function setDefault (string $alias) : void {
        $this->__default = $this->aliasExists ($alias);
    }

    final public function getAliases () : array {
        $alias = [];

        foreach ($this->__alias as $aliases) {
            $alias[] = $aliases[0];
        }

        return $alias;
    }

    final public function getPath (string $alias = null) : string {
        $numberOfAlias = $alias ? $this->aliasExists($alias) : $this->__default;

        return $this->__alias[$numberOfAlias][1];
    }

    final public function getFile (string $alias = null) {
        $numberOfAlias = $alias ? $this->aliasExists($alias) : $this->__default;

        if ($this->__files[$numberOfAlias] === null) {
            $this->__currentFile = $numberOfAlias;
            $this->open ();
            $this->__currentFile = null;
        }

        return $this->__files[$numberOfAlias];
    }

    final public function open (string $alias = null, string $mode = null) : void {
        $numberOfAlias = $alias ? $this->aliasExists($alias) : ($this->__currentFile ?? $this->__default);
        $_alias = $this->__alias[$numberOfAlias];

        if ( !($this->__files[$numberOfAlias] = fopen($_alias[1], ($mode ?? ($this->__currentFileMode ?? $_alias[2])))) ) {
            throw new Exception ("Error opening file");
        }
    }

    final public function openAll (array $modes = []) : void {
        foreach ($this->__alias as $n => $alias) {
            $this->__currentFile = $n;
            $this->__currentFileMode = $modes[$n] ?? null;

            $this->open ();
        }

        $this->__currentFile = null;
        $this->__currentFileMode = null;
    }

    final public function close (string $alias = null) : void {
        $numberOfAlias = $alias ? $this->aliasExists($alias) : ($this->__currentFile ?? $this->__default);

        if ($this->__files[$numberOfAlias] === null) {
            return;
        }

        if (!fclose($this->__files[$numberOfAlias])) {
            throw new Exception ("Failed to close file: '{$this->__alias[$numberOfAlias][0]}'");
        }

        $this->__files[$numberOfAlias] = null;

        if ($this->persistent) { return; }

        if (!unlink($this->__alias[$numberOfAlias][1])) {
            throw new Exception ("Failed to delete file: '{$this->__alias[$numberOfAlias][0]}'");
        }
    }

    final public function closeAll () : void {
        foreach ($this->__files as $n => $file) {
            $this->__currentFile = $n;
            $this->close ();
        }

        $this->__currentFile = null;
    }

    final public function getLine (string $alias = null) : string|null {
        $numberOfAlias = $alias ? $this->aliasExists($alias) : $this->__default;

        if ($this->__files[$numberOfAlias] === null) {
            $this->__currentFile = $numberOfAlias;
            $this->open ();
            $this->__currentFile = null;
        }

        if (feof($this->__files[$numberOfAlias])) {
            return null;
        }

        return trim (fgets($this->__files[$numberOfAlias]));
    }

    final public function write (string $alias, string $text = "") : void {
        $numberOfAlias = $this->aliasExists ($alias);

        if ($this->__files[$numberOfAlias] === null) {
            throw new Exception ("The file is not open:: '{$this->__alias[$numberOfAlias][0]}'");
        }

        if ( !(fwrite($this->__files[$numberOfAlias], $text)) ) {
            throw new Exception ("Error trying to write to file: '{$this->__alias[$numberOfAlias][0]}'");
        }
    }

    final public function move (string $alias, string $path) : void {
        $numberOfAlias = $this->aliasExists ($alias);
        $_alias = $this->__alias[$numberOfAlias];

        if ($this->__files[$numberOfAlias] !== null) {
            if (!fclose($this->__files[$numberOfAlias])) {
                throw new Exception ("Failed to close file: '{$_alias[0]}'");
            }

            $this->__files[$numberOfAlias] = null;
        }

        if (!file_exists($_alias[1])) {
            throw new Exception ("Non-existent file: '{$_alias[0]}'");
        }

        if (!rename($_alias[1], $path)) {
            throw new Exception ("Error moving file: '{$_alias[0]}'");
        }

        $this->__alias[$numberOfAlias] = [
            $_alias[0],
            realpath ($path),
            $_alias[2]
        ];
    }

    final protected function aliasExists (string $alias) : int {
        $a = parent::aliasExists ($alias);

        if ($a < 0) {
            throw new Exception ("Unknown alias");
        }

        return $a;
    }
}

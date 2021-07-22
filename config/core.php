<?php
/*Configuración general del framework*/
namespace atomic;

abstract class CONFIG
{
    protected const CTRL_PATH = __DIR__."/../controllers";
    protected const UTF8 = false;
    protected array $headers = [
        
    ];
}

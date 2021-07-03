<?php

namespace atomic;

define ("header", apache_request_headers ());

abstract class CONFIG
{
    protected const CTRL_PATH = __DIR__."/../controllers";
    protected const UTF8 = false;
    protected array $headers = [
        "user" => header["user"] ?? null,
        "session" => header["session"] ?? null
    ];
}
<?php

namespace App\Service;

class HelloService
{
    public function __construct()
    {
    }

    public function hello($name): string
    {
        return 'Hello, ' . $name;
    }
}
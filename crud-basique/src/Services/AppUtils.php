<?php


namespace App\Services;


use phpDocumentor\Reflection\Types\String_;

class AppUtils
{
    public function capitalize(string $string): string
    {
        return ucfirst(mb_strtolower($string));
    }
}
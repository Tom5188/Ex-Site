<?php

namespace App\Utils;

use Illuminate\Support\Facades\Config;

class Hash
{
    public static function createCode(){
        $bin = random_bytes(20);
        $privateKey = bin2hex($bin);
        return $privateKey;
    }
}
<?php

namespace App\Services\Traits;

use Illuminate\Support\Str;

trait IdTrait{

    public function hashID(int $length = 32){
        return bin2hex(random_bytes($length));
    }

    public function index()
    {
        return (string) Str::uuid()/*  . '-' . time() */;
    }

    public function index2()
    {
        return (string) Str::orderedUuid() /* . '-' . time() */;
    }

}
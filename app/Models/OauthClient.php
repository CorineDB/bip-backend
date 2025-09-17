<?php

namespace App\Models;

use Laravel\Passport\Client as PassportClient;

class OauthClient extends PassportClient
{
    public $incrementing = false;
    protected $keyType = 'string';
}

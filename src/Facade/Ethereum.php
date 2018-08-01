<?php

namespace Jcsofts\LaravelEthereum\Facade;

use Illuminate\Support\Facades\Facade;

class Ethereum extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Jcsofts\LaravelEthereum\Lib\Ethereum::class;
    }
}

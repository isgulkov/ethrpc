<?php

namespace IsGulkov\EthRPC\Facade;

use Illuminate\Support\Facades\Facade;

class EthRPC extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \IsGulkov\EthRPC\Lib\EthRPC::class;
    }
}

<?php

namespace NewIDC\Plugin\Facade;

use Illuminate\Support\Facades\Facade as Base;

class PluginManager extends Base
{
    protected static function getFacadeAccessor()
    {
        return 'PluginManager';
    }
}
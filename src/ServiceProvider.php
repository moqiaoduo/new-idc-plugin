<?php

namespace NewIDC\Plugin;

use Illuminate\Support\ServiceProvider as Base;

class ServiceProvider extends Base
{
    public function register()
    {
        $this->app->bind('PluginManager',function () {
            return new Manager();
        });
    }
}
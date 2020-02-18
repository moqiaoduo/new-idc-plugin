<?php

namespace NewIDC\Plugin\Events;

use Illuminate\Queue\SerializesModels;

class ServiceActivate
{
    use SerializesModels;

    public $service;

    public function __construct($service)
    {
        $this->service = $service;
    }
}
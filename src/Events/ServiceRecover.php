<?php

namespace NewIDC\Plugin\Events;

use Illuminate\Queue\SerializesModels;

class ServiceRecover
{
    use SerializesModels;

    public $service;

    public function __construct($service)
    {
        $this->service = $service;
    }
}
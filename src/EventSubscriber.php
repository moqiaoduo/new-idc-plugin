<?php

namespace NewIDC\Plugin;

class EventSubscriber
{
    /**
     * 为订阅者注册监听器.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(

        );
    }
}
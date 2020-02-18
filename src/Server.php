<?php

namespace NewIDC\Plugin;

abstract class Server implements Plugin
{
    /**
     * 插件名称
     *
     * @var string
     */
    protected $name;

    /**
     * 插件别名
     *
     * @var string
     */
    protected $slug;

    /**
     * 插件版本
     *
     * @var string
     */
    protected $version;

    /**
     * 插件说明
     *
     * @var string
     */
    protected $description;

    public function info(): array
    {
        return [
            'name'=>$this->name,
            'slug'=>$this->slug,
            'version'=>$this->version,
            'description'=>$this->description
        ];
    }

    public function hook()
    {
        // TODO: Implement hook() method.
    }
}
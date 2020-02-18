<?php

namespace NewIDC\Plugin;

/**
 * 插件将遵循Laravel扩展包开发方法开发
 * 在服务提供类boot方法中注册插件，才能被监听者触发
 * 很残念的是，这个管理类的作用只是监测插件是否允许执行（
 * 为了让事件订阅者正常工作，将会先初始化再注册
 */
class Manager
{
    /**
     * 插件列表
     *
     * @var array
     */
    private static $plugins = [];

    /**
     * 启用的插件
     *
     * @var array
     */
    private static $ena_plugins = [];

    public static function init()
    {
        self::$ena_plugins=json_decode(getOption('ena_plugins'),true)?:[];
    }

    /**
     * 不是所有的插件都能手动开关
     * Server插件只要存在即开启
     *
     * @param $plugin
     */
    public static function register(Plugin &$plugin)
    {
        // 传入plugin对象，自动注册hook以及加入插件列表
        $info=$plugin->info();self::$plugins[]=$info;
        if ($plugin instanceof Server && !self::checkEnable($info['slug']))
            self::$ena_plugins[]=$info['slug'];
    }

    /**
     * 列出所有插件
     *
     * @return array
     */
    public static function pList()
    {
        return self::$plugins;
    }

    public static function checkEnable($slug)
    {
        return in_array($slug,self::$ena_plugins);
    }
}
<?php

namespace NewIDC\Plugin;

use Illuminate\Support\Arr;

/**
 * 插件将遵循Laravel扩展包开发方法开发
 * 插件通过调用register方法注册hook
 * 程序则在指定位置触发hook
 */
class Manager
{
    /**
     * 插件列表
     *
     * @var array
     */
    private $plugins = [];

    /**
     * 启用插件列表
     *
     * @var array
     */
    private $ena_plugins;

    /**
     * 定义钩子列表
     *
     * @var array
     */
    private $hooks = [];

    public function __construct()
    {
        $this->ena_plugins=json_decode(getOption('ena_plugins'),true)?:[];
    }

    /**
     * 不是所有的插件都能手动开关
     * Server插件只要存在即开启
     * 插件在boot方法务必执行一下，否则无法正常识别
     *
     * @param Plugin $plugin 插件对象
     */
    public function register($plugin)
    {
        // 传入plugin对象，自动注册hook以及加入插件列表
        $this->plugins[]=$info=$plugin->info();
        if ($plugin instanceof Server || ($ena=$this->checkEnable($info['slug']))) {
            if (!($ena??false)) // 如果没有加入启用列表，则加入
                $this->ena_plugins[]=$info['slug'];
            foreach ((array) $plugin->hook() as $hook) {
                $p=['plugin'=>$plugin];
                if (isset($hook['func'])) {
                    $p['func']=$hook['func'];
                } else {
                    $p['method']=$hook['method'];
                }
                $this->hooks[$hook['hook']][]=$p;
            }
        }
    }

    /**
     * 列出所有插件
     *
     * @return array
     */
    public function pList()
    {
        return $this->plugins;
    }

    /**
     * 检查插件是否启用
     *
     * @param $slug
     * @return bool
     */
    public function checkEnable($slug)
    {
        return in_array($slug,$this->ena_plugins);
    }

    /**
     * 触发钩子
     *
     * @param string $hook_name
     * @param string|callable|null $default
     * @param mixed $data
     * @param bool $last
     * @param bool $returnArray
     * @return mixed
     */
    public function trigger($hook_name, $default=null, $data=null, $last=false, $returnArray=false)
    {
        $hasRun=false;$return=null;
        $hooks=$this->hooks[$hook_name]??[];
        if ($returnArray) $return=[];
        if ($last) {
            $hook=Arr::last($hooks);
            $result=$this->singleRun($hook,$data,$hasRun);
            if ($hasRun) {
                if ($returnArray) $return=[$result];
                else $return=$result;
            }
        } else {
            foreach ((array) $hooks as $hook) {
                $result=$this->singleRun($hook,$data,$hasRun);
                if ($hasRun) {
                    if ($returnArray) $return[]=$result;
                    else $return.=$result;
                }
            }
        }
        if (!$hasRun && is_callable($default)) {
            $return=$default($data);
            if ($returnArray) $return=[$return];
        }
        return $return;
    }

    protected function singleRun($hook, $data, &$hasRun)
    {
        if (isset($hook['func']) && is_callable($hook['func'])) {
            $hasRun=true;
            return $hook['func']($data);
        }
        if (isset($hook['method']) && is_callable([$hook['plugin'],$hook['method']])) {
            $hasRun=true;
            return $hook['func']($data);
        }
        return null;
    }
}
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
     * 服务器插件列表
     *
     * @var array
     */
    private $server_plugins = [];

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
    public function register(Plugin $plugin)
    {
        // 传入plugin对象，自动注册hook以及加入插件列表
        $id=get_class($plugin);$this->plugins[$id]=$plugin->info();
        if (($isServer=($plugin instanceof Server)) || ($ena=$this->checkEnable($id))) {
            if (!($ena??false)) // 如果没有加入启用列表，则加入
                $this->ena_plugins[]=$id;
            if ($isServer) $this->server_plugins[]=$id;
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
    public function getList()
    {
        return $this->plugins;
    }

    /**
     * 列出启用的插件
     *
     * @return array
     */
    public function getEnableList()
    {
        return $this->ena_plugins;
    }

    /**
     * 获取服务器插件列表
     *
     * @return array
     */
    public function getServerPluginList()
    {
        return $this->server_plugins;
    }

    /**
     * 检查插件是否启用
     *
     * @param $id
     * @return bool
     */
    public function checkEnable($id)
    {
        return in_array($id,$this->ena_plugins);
    }

    /**
     * 触发钩子
     *
     * @param array $params
     * @return mixed
     */
    public function trigger($params)
    {
        $default_params=[
            'default_func'=>'',
            'data'=>null,
            'last'=>false,
            'returnArray'=>false
        ];
        if (is_array($params)) {
            $params=array_merge($default_params,$params);
        } else {
            $params=$default_params;
            $params['hook']=$params;
        }
        $hasRun=false;$return=null;
        $hooks=$this->hooks[$params['hook']]??[];
        $data=$params['data'];
        $returnArray=$params['returnArray'];
        if ($returnArray) $return=[];
        if ($params['last']) {
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
        if (!$hasRun && is_callable($func=$params['default_func'])) {
            $return=$func($data);
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
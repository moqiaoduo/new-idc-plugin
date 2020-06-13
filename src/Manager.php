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

    /**
     * composer.lock缓存
     *
     * @var array
     */
    private $_composer_lock = [];

    /**
     * 指向一个实际变量
     *
     * @var bool
     */
    private $_plugged;

    public function __construct()
    {
        $this->ena_plugins = Arr::wrap(json_decode(getOption('ena_plugins'), true));
    }

    /**
     * 不是所有的插件都能手动开关
     * Server插件只要存在即开启
     * 插件在boot方法务必注册一下，否则无法正常识别
     *
     * @param Plugin $plugin 插件对象
     */
    public function register(Plugin $plugin)
    {
        // 传入plugin对象，自动注册hook以及加入插件列表
        $id = get_class($plugin);

        // 优先读取缓存
        if (empty($this->_composer_lock))
            $this->_composer_lock = $composer_lock =
                json_decode(file_get_contents(base_path('composer.lock')), true);
        else
            $composer_lock = $this->_composer_lock;

        $this->plugins[$id] = $plugin->info();
        foreach (($composer_lock['packages'] ?? []) as $package) {
            if ($package['name'] === ($this->plugins[$id]['composer'] ?? null)) {
                $this->plugins[$id]['version'] = $package['version'];
                break;
            }
        }
        if (($isServer = ($plugin instanceof Server)) || $this->isEnable($id)) {
            if ($isServer) $this->server_plugins[] = $id;
            foreach (Arr::wrap($plugin->hook()) as $hook) {
                $hook_name = $hook['hook'];
                $p = ['plugin' => $plugin];
                if (isset($hook['func'])) {
                    $p['func'] = $hook['func'];
                } else {
                    $p['method'] = $hook['method'];
                }
                $this->hooks[$hook_name][] = $p;
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
     * 列出启用的插件（不包括服务器插件）
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
     * 是否为服务器插件
     *
     * @param $id
     * @return bool
     */
    public function isServerPlugin($id)
    {
        return in_array($id, $this->server_plugins);
    }

    /**
     * 插件是否启用
     *
     * @param $id
     * @return bool
     */
    public function isEnable($id)
    {
        return in_array($id, $this->ena_plugins);
    }

    /**
     * 触发钩子
     * 结果以数组形式返回
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $hasRun = false;

        $return = [];

        $hooks = $this->hooks[$name] ?? [];

        foreach ((array)$hooks as $hook=>$callable) {
            if (is_callable($callable)) {
                $return[] = call_user_func($callable, ...$arguments);
                $hasRun = true;
            }
        }

        $this->_plugged = $hasRun;

        return $return;
    }

    /**
     * 绑定结果变量
     *
     * @param $var
     * @return $this
     */
    public function trigger(&$var)
    {
        $var = false;

        $this->_plugged = &$var;

        return $this;
    }

    /**
     * 为了不经过门面传输参数，先获取原始对象
     *
     * @return $this
     */
    public function handler()
    {
        return $this;
    }
}
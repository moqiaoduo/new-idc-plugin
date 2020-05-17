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
        $composer_lock = json_decode(file_get_contents(base_path('composer.lock')), true);
        $this->plugins[$id] = $plugin->info();
        foreach (($composer_lock['packages'] ?? []) as $package) {
            if ($package['name']===($this->plugins[$id]['composer'] ?? null)) {
                $this->plugins[$id]['version'] = $package['version'];
                break;
            }
        }
        if (($isServer = ($plugin instanceof Server)) || ($ena = $this->checkEnable($id))) {
            if (!($ena ?? false)) // 如果没有加入启用列表，则加入
                $this->ena_plugins[] = $id;
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
        return in_array($id, $this->ena_plugins);
    }

    /**
     * 触发钩子
     * 结果以数组形式返回
     *
     * @param string $hookName
     * @param mixed $data
     * @param callable|null $default
     * @return mixed
     */
    public function trigger($hookName, $data = null, $default = null)
    {
        $hasRun = false;
        $hooks = $this->hooks[$hookName] ?? [];
        foreach ((array)$hooks as $hook) {
            if (isset($hook['func']) && is_callable($hook['func'])) {
                $hasRun = true;
                $return[] = $hook['func']($data);
            } elseif (isset($hook['method']) && is_callable([$hook['plugin'], $hook['method']])) {
                $hasRun = true;
                $return[] = $hook['plugin']->$hook['method']($data);
            }
        }
        if (!$hasRun) {
            if (is_callable($default)) {
                if (is_array($default)) {
                    [$obj, $method] = $default;
                    $return[] = $obj->$method($data);
                } else {
                    $return[] = $default($data);
                }
            }
        }
        return $return ?? [];
    }
}
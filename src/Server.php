<?php

namespace NewIDC\Plugin;

use App\Models\Product;
use App\Models\Service;

abstract class Server implements Plugin
{
    /**
     * 插件名称
     *
     * @var string
     */
    protected $name;

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

    /**
     * 类型默认为其他
     *
     * @var string
     */
    protected $type = 'others';

    /**
     * 服务模型
     *
     * @var Service
     */
    protected $service;

    /**
     * 服务器模型
     *
     * @var \App\Models\Server
     */
    protected $server;

    /**
     * 产品模型
     *
     * @var Product
     */
    protected $product;

    /**
     * 服务激活时的操作
     *
     * 所有操作均需要返回：
     * ['code'=>状态码(0为成功标准),'message'=>错误信息]
     * 状态码和错误信息主要用于调试
     * 其他返回值不接受
     * 如果需要修改extra字段，直接更新就行了
     *
     * @return array
     */
    abstract public function activate();

    /**
     * 服务暂停时的操作
     *
     * @return array
     */
    abstract public function suspend();

    /**
     * 服务恢复时的操作
     *
     * @return array
     */
    abstract public function recover();

    /**
     * 服务销毁时的操作
     *
     * @return array
     */
    abstract public function terminate();

    /**
     * 修改密码时的操作
     *
     * @param string $password
     * @return array
     */
    abstract public function changePassword($password);

    /**
     * 服务信息（显示在服务详情中）
     *
     * @param bool $ajax
     * @return array
     */
    abstract public function serviceInfo($ajax = false);

    /**
     * 用量查询
     *
     * @return array
     */
    abstract public function usage();

    /**
     * 升降级操作
     *
     * @return array
     */
    abstract public function upgradeDowngrade();

    /**
     * 改变服务状态
     *
     * @param $status
     * @return bool
     */
    public function changeServiceStatusTo($status)
    {
        switch ($status) {
            case 'active':
                if ($this->service->status === 'suspended') {
                    $result = $this->recover();
                } else {
                    $result = $this->activate();
                }
                break;
            case 'suspended':
                $result = $this->suspend();
                break;
            case 'terminated':
                $result = $this->terminate();
                break;
            default:
                $result = false;
        }
        if ($result['code'] === 0)
            $this->service->update(['status' => $status]);
        return $result;
    }

    public function userLogin()
    {
        return "None";
    }

    public function adminLogin()
    {
        return "None";
    }

    /**
     * 其他设置
     *
     * @return array
     */
    static public function otherConfig()
    {
        return array();
    }

    /**
     * 产品配置
     *
     * @return array
     */
    static public function productConfig()
    {
        return array();
    }

    /**
     * 用户前端设置
     *
     * @return array
     */
    static public function userConfig()
    {
        return array();
    }

    /**
     * 升降级产品设置
     *
     * @return array
     */
    static public function upgradeDowngradeConfig()
    {
        return array();
    }

    /**
     * 升降级前端设置
     *
     * @return array
     */
    static public function userUpgradeDowngradeConfig()
    {
        return array();
    }

    /**
     * 域名设置
     *
     * @return array
     */
    static public function domainConfig()
    {
        return array();
    }

    /**
     * 默认端口
     *
     * @return int
     */
    protected function defaultPort()
    {
        return 2086;
    }

    /**
     * 为了不影响插件注册，不采用构造函数来初始化数据
     *
     * @param $product
     * @param $service
     * @param $server
     */
    public function init(?Product $product, ?Service $service, ?\App\Models\Server $server)
    {
        $this->product = $product;
        $this->service = $service;
        $this->server = $server;
    }

    /**
     * 使用属性来定义插件信息
     * 当前版本使用类名（带命名空间）作为唯一识别符
     *
     * @return array
     */
    public function info(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description
        ];
    }

    /**
     * 即使没有注册hook，只要手动注册了，都会出现在插件列表，
     * 只是无法被作为钩子调用，但是NewIDC内部有一套其他的处理程序
     * 如果有特殊需求也可以注册钩子
     *
     * @return array
     */
    public function hook(): array
    {
        return [];
    }
}
<?php

namespace NewIDC\Plugin;

interface Plugin
{
    /**
     * 插件信息至少包括以下信息：
     * 插件名称
     * 建议：插件版本、插件说明
     * 其他可自定义 不过一般不会使用
     *
     * @return array
     */
    public function info(): array ;

    /**
     * 定义hook
     * 格式： 键值对，例如
     * ['demo'=>[$this, 'demo']]
     * 其中 键'demo' 指的是Hook名称
     * 而值中的 [$this, 'demo'] 指的是当前对象的 demo 方法
     * 具体的原理，请看 Manager 中 __call 方法的实现
     * 其实就是相当于 call_user_func 的参数
     *
     * @return array
     */
    public function hook(): array ;
}
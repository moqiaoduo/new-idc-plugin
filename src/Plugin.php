<?php

namespace NewIDC\Plugin;

interface Plugin
{
    /**
     * 插件信息至少包括以下信息：
     * 插件名称
     * 插件别名（英文）
     * 建议：插件版本、插件说明
     * 其他可自定义 不过一般不会使用
     *
     * @return array
     */
    public function info(): array ;

    /**
     * 插件所定义的事件
     * 需要返回完整的命名空间和类名
     *
     * @return array
     */
    public function events(): array ;
}
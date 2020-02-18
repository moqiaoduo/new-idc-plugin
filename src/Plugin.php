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
     * 定义hook
     * 格式： ['hook'=>'','func'=>'']
     *
     * @return mixed
     */
    public function hook();
}
# new-idc-plugin
给[NewIDC](https://github.com/moqiaoduo/NewIDC)写的一个简易插件管理器

## How to use
安装本扩展到NewIDC，要求基础框架Laravel 6.2，且存在options表

```
composer require moqiaoduo/new-idc-plugin
```

在插件的服务提供者的boot方法中调用 PluginManager::register

在hook位置调用 PluginManager::trigger

如果有插件列表，调用 PluginManager::getList 获取列表，调用 PluginManager::checkEnable 获取插件是否启用

That's all.

## License

采用 [MIT License](https://opensource.org/licenses/MIT)
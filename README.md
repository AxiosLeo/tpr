## TPR Framework 3.0 (beta)

前言: 用过很多的 php 框架，也基于 thinkphp5.0.9 改过框架内核，但是总觉得不够灵活自由，所以就有了这一版重新改写的框架。
框架继承了很多一些其他的框架的特点，其中受 thinkphp 的影响是最深的，设计的宗旨是"轻量、灵活、高效"。

当前为 beta 版本，不建议在生产环境中使用，仅作技术交流使用，以后会逐渐更新文档和使用案例

> 交流QQ群：521797692

## 安装使用

``` shell
composer require axios/tpr:dev-3.0-beta
```

* 入口文件示例
```
namespace app;

use tpr\App;

require_once __DIR__ . '/../vendor/autoload.php';

$app_namespace = "App\\";      // 设置应用基础命名空间
App::run(app_namespace, true); // true : 调试模式 ; false 生产模式

```

## 简单应用

> [github.com/AxiosCros/tpr-app](https://github.com/AxiosCros/tpr-app)

## 开源协议
  > 遵循Apache2开源协议发布，并提供免费使用
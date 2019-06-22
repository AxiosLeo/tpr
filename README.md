## TPR Framework 3.0 (alpha)

前言: 用过很多的 php 框架，也基于 thinkphp5.0.9 改过框架内核，但是总觉得不够灵活自由，所以就有了这一版重新改写的框架。
框架继承了很多一些其他的框架的特点，其中受 thinkphp 的影响是最深的，设计的宗旨是"轻量、灵活、高效"。

当前为 alpha 版本，不建议在生产环境中使用，以后会逐渐更新文档和使用案例

> 交流QQ群：521797692

## 安装使用

> 以前的 axios/tpr 包不再维护，已弃用

``` shell
composer require axios/tpr:dev-3.0-alpha
```

* 入口文件示例
```
namespace app;

use tpr\App;

require_once __DIR__ . '/../vendor/autoload.php';

// true : 调试模式 ; false 生产模式
App::mode(true)->run();
// or App::run();


// 设置应用基础命名空间
// App::run($app_namespace = "App\\");

```

* 启动 web 服务

> index.php 为入口文件

```shell
# 进入具有入口文件的项目目录
cd /path/to/project/public/

# 启动服务
php -S localhost:8088
```

* 访问
> [locahost::8088/index.php](locahost::8088/index.php])

## 开源协议
  > 遵循Apache2开源协议发布，并提供免费使用
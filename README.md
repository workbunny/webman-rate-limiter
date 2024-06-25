<p align="center"><img width="260px" src="https://chaz6chez.cn/images/workbunny-logo.png" alt="workbunny"></p>

**<p align="center">workbunny/webman-rate-limiter</p>**

**<p align="center">🐇  Webman plugin for sqlite database service current limiting solution. 🐇</p>**

# Webman plugin for sqlite database service current limiting solution.

<div align="center">
    <a href="https://github.com/workbunny/webman-rate-limiter/actions">
        <img src="https://github.com/workbunny/webman-rate-limiter/actions/workflows/CI.yml/badge.svg" alt="Build Status">
    </a>
    <a href="https://github.com/workbunny/webman-rate-limiter/releases">
        <img alt="Latest Stable Version" src="http://poser.pugx.org/workbunny/webman-rate-limiter/v">
    </a>
    <a href="https://github.com/workbunny/webman-rate-limiter/blob/main/composer.json">
        <img alt="PHP Version Require" src="http://poser.pugx.org/workbunny/webman-rate-limiter/require/php">
    </a>
    <a href="https://github.com/workbunny/webman-rate-limiter/blob/main/LICENSE">
        <img alt="GitHub license" src="http://poser.pugx.org/workbunny/webman-rate-limiter/license">
    </a>
</div>



## 为什么不选择redis做服务限流

- redis 缓存和限流都是非常👍棒的工具,是任何项目的不二自选。
- 在高可用方面。redis一旦内存到达顶峰。也会存在redis服务崩溃的情况（redis 💥炸了）
- 基于文件数据库`sqlite`, 绝大部分的IO都是在磁盘。所以本项目可以作为限流的兜底政策和熔断策略

## 简介
基于`SQLite`文件数据库的令牌桶`TOKEN`限流,无需依赖其他服务载体。`轻量化`，`颗粒度`更细

## 特征
 ✅️&nbsp; 文件数据库SQLite



## 用法
```shell
composer require workbunny/webman-rate-limiter
```

### 在Webman中使用

1. 配置app.php

```php
return [
    'enable' => true,

    /** sqlite 数据库配置 */
    'sqlite' => [
        /** 数据库文件地址 */
        "dbFilePath" => dirname(__DIR__) . "/webman-rate-limiter/ExampleDb/rate-limit.db",
        /** 加密秘钥 */
        "encryptionKey" => ""
    ],
    /** 令牌桶配置 */
    'bucket' => [
        /** 桶的最大容量 */
        "capacity" => 60,
        /** 满桶的所需时间 */
        "seconds" => 60
    ]

];
```

2. 在路由中间件中使用

```php
class RateLimiter implements MiddlewareInterface
{

    public function process(Request $request, callable $handler): Response
    {
        
        $token = $request->getRealIp(false);
//        $token = $request->path();
//        $token = $request->post("uuid");
        $rate = (new \Workbunny\WebmanRateLimiter\RateLimiter() )->handle($token);

        return $rate ?   $handler($request) :   \response(
            ["error_msg" => "请求过于频繁"] ,429  );
    }

}
```
3. 在任意地方使用

```php
public function test(Request $request):Response
    {

        $rate = (new RateLimiter() )->handle("192.168.10.9");

        if ($rate)
        {
            return \response(["msg"=>"success"] , 200,[]);
        }

        return \response(["msg"=>"请求频繁"] , 429,[]);
    }
```




## 什么是令牌桶？

&emsp;&emsp;首先，我们不妨抽象出这样一个场景：有一个装满水的水桶，不断有人前来取水，同时水龙头一直开着为水桶灌水。若桶里无水，则禁止取水！

&emsp;&emsp;我们设定一个标准，即一分钟内，每人每 10 秒可取一次水。如此一来，便能确保桶里始终有水，且每个人都能持续取水。这相当于在这 10 秒内，水龙头流出的水量恰好满足一人的取水量。

&emsp;&emsp;那么，倘若其中某一人的取水频次变为 5 秒，此时我们应告知他，在这一分钟内，若继续保持这样的频次取水，那么他还剩下 5 次取水机会。

&emsp;&emsp;如果他不听劝，每取一次水便会减少一次机会。当时间过去 30 秒后，他的取水机会已然耗尽。此时应告知他远离此地，而他只能眼睁睁看着别人取水，需再等待一分钟方可重新取水。







### TO
<div align="center">
        <img alt="令牌桶限流结构图" src="https://github.com/workbunny/webman-rate-limiter/blob/main/material/process-1.jpg?raw=true">
</div>

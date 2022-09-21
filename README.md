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

1. [ ] **未完成更新**


## 为什么不选择redis做服务限流

- redis 缓存和限流都是非常👍棒的工具。
- 在高可用方面。redis一旦内存到达顶峰。也会存在redis服务崩溃的情况（redis 💥炸了）
- 基于文件数据库`sqlite`, 绝大部分的IO都是在磁盘。所以本项目可以作为限流的兜底政策和熔断策略

## 简介


## 特征
 ✅️&nbsp; 文件数据库


## 用法

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
            return response_success([$rate]);
        }

        return response_error("429|429");
    }
```


## 鸣谢

## 什么是令牌桶？

&emsp;&emsp;首先，我们可以先抽象出一个场景，一个水桶装有水，陆陆续续有人向水桶取水，当然水龙头一直开着并往水桶里灌水。


&emsp;&emsp;我们定义一个标准，一分钟内一个人10秒才能取一次水。那么这样就能保证桶里一直有水，按照这样的频次能保证每个人能一直取水。


&emsp;&emsp;那么，当一个人取水的频次变成5秒，这个时候我们就要告诉他在这一分钟内你还剩**5**次取水机会。

&emsp;&emsp;如果他不听，每次取水就会少一次机会， 当时间过去30秒后他已经没有取水机会了。告诉他**让他滚**，这个时候他就只能眼睁睁的看着别人取水了。他得再等**一分钟**才能去取水。

&emsp;&emsp;综上所述！`一分钟内一个人10秒才能取一次水`......

<div align="center">
        <img alt="令牌桶限流结构图" src="https://github.com/workbunny/webman-rate-limiter/blob/main/material/process-1.jpg?raw=true">
</div>

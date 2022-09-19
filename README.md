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

- redis 缓存和限流都是非常👍棒的工具。
- 在高可用方面。redis一旦内存到达顶峰。也会存在redis服务崩溃的情况（redis 💣炸了）
- 基于文件数据库`sqlite`, 绝大部分的IO都是在磁盘。所以本项目可以作为限流的兜底政策和熔断策略

## 简介

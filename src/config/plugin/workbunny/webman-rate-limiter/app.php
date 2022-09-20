<?php
return [
    'enable' => true,

    /** sqlite 数据库配置 */
    'sqlite' => [
        /** 数据库文件地址 */
        "dbFilePath" => dirname(__DIR__) . "/ExampleDb/rate-limit.db",
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
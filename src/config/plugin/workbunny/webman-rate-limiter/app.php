<?php
return [
    'enable' => true,

    'sqlite' => [
        "dbFilePath" => dirname(__DIR__) . "/ExampleDb/rate-limit.db",
        "encryptionKey" => ""
    ],
    'bucket' => [
        "capacity" => 60,
        "seconds" => 60
    ]

];
<?php
return [
    'enable' => true,

    'sqlite' => [
        "dbFilePath" => dirname(__DIR__) . "/src/db/.db",
        "encryptionKey" => ""
    ],
    'bucket' => [
        "capacity" => 60,
        "seconds" => 60
    ]

];
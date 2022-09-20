<?php

namespace Workbunny\WebmanRateLimiter;

use SQLite3Result;
use WorkBunny\Storage\Driver;

/**
 * @desc 基于SQLite的 令牌桶缓存机制
 * @date 2022/9/19
 * @author sunsgne
 */
class StorageClient
{

    /** @var Driver sqlite驱动 */
    protected static Driver $client;

    /** @var string 表名及数据文件名称 */
    protected static string $dbFileName = 'rate-limit';


    protected static int $s2ns = 1000000000;

    public function __construct()
    {
        if (!(self::$client ?? null instanceof Driver)) {
            self::$client = new Driver([
                'filename'      => dirname(__DIR__) . "/src/db/" . self::$dbFileName . ".db",
                'flags'         => SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
                'encryptionKey' => ""
            ]);
            self::$client->create(self::$dbFileName, [
                'key'         => [
                    'VARCHAR',
                    'PRIMARY KEY',
                    'NOT NULL',
                ],
                'capacity'    => [
                    'INT(5)',
                    'NOT NULL',
                ],
                'created_at' => [
                    'timestamp'
                ],
                'updated_at' => [
                    'timestamp'
                ],

            ], [
                'CREATE INDEX IF NOT EXISTS `rate-limit-key` ON `rate-limit` (`key`);'
            ]);
        }
    }

    /**
     * 初始化存储库
     * @return void
     * @datetime 2022/9/19 17:14
     * @author sunsgne
     */
    protected function initDB()
    {
        /** @var $res * 查询表是否存在 */
        $res = self::$client->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name = '".self::$dbFileName."';");
        $table = 0;
        if ($res instanceof SQLite3Result) {
            $table = $res->fetchArray()[0] ?? 0;
        }

        /** 表不存在，新建表 */
        if (0 === $table) {
            self::$client->create(self::$dbFileName, [
                'key'         => [
                    'VARCHAR',
                    'PRIMARY KEY',
                    'NOT NULL',
                ],
                'capacity'    => [
                    'INT(5)',
                    'NOT NULL',
                ],
                'created_at' => [
                    'timestamp'
                ],
                'updated_at' => [
                    'timestamp'
                ],

            ], [
                'CREATE INDEX `rate-limit-key` ON `rate-limit` (`key`);'
            ]);


        }

    }


    /**
     * @param string $clientIp
     * @param int $capacity
     * @param int $seconds
     * @return int|mixed
     * @datetime 2022/9/19 17:13
     * @author sunsgne
     */
    public function handle(string $key, int $capacity, int $seconds)
    {
        $nowTime = hrtime(true);

        $res = self::$client->query('SELECT `key` , `capacity` , `updated_at` FROM `rate-limit` WHERE `key` = "' . $key . '";');

        $ipResult = [];
        if ($res instanceof SQLite3Result) {
            $ipResult = $res->fetchArray();
        }


        /** 存在此前KEY的请求 */
        if (isset($ipResult) and !empty($ipResult)) {
            if (($ipResult["updated_at"] + ($seconds * self::$s2ns) ) < $nowTime) {
                /** 不在限流时间内 ,重置限流请求次数，并正常返回  */
                $this->resetBucketTime($key , $capacity - 1 , $nowTime);
                return intval($capacity - 1);
            }


            $time_passed = ($nowTime - $ipResult["updated_at"]) / self::$s2ns;
            $allow = $ipResult["capacity"];
            $allow += $time_passed * ($capacity / $seconds);

            $capacity = min($capacity , $allow);



            if ($capacity >= 1)
            {
                $this->updateBucket($key , $capacity -1 , $nowTime);
                return (int)$capacity -1;
            }
            /** 限流  */
            return 0;
        }

        /** 当前请求写入库 并正常返回 */

       $this->createBucket($key , $capacity , $nowTime);

        return intval($capacity - 1);

    }

    /**
     *
     * @param string $key 关键key
     * @param int $capacity 容量
     * @param int $time 时间戳
     * @return void
     * @datetime 2022/9/20 11:56
     * @author zhulianyou
     */
    public function createBucket(string $key ,int $capacity , int $time)
    {
        self::$client->insert(self::$dbFileName, [
            'key'         => $key,
            'capacity'    => $capacity,
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }


    /**
     * @param string $key
     * @param int $capacity
     * @param int $time
     * @return void
     * @datetime 2022/9/20 13:57
     * @author zhulianyou
     */
    public function updateBucket(string $key ,int $capacity , int $time)
    {
        self::$client->query('UPDATE `rate-limit` SET `capacity` = "' . $capacity . '"  , `updated_at` = "' . $time . '"  WHERE `key` = "' . $key . '";');
    }


    /**
     * @param string $key
     * @param int $capacity
     * @param int $time
     * @return void
     * @datetime 2022/9/20 13:57
     * @author zhulianyou
     */
    public function resetBucketTime(string $key ,int $capacity , int $time)
    {
        self::$client->query('UPDATE "' .self::$dbFileName. '" SET `capacity` =  "' . $capacity . '" , `updated_at` = "' . $time . '" WHERE `key`= "' . $key . '";');
    }


}
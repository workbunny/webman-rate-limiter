<?php

namespace Workbunny\WebmanRateLimiter;

use SQLite3Result;
use WorkBunny\Storage\Driver;

/**
 * @desc 基于SQLite的 令牌桶算法机制
 * @date 2022/9/19
 * @author sunsgne
 */
class RateLimiter
{

    /** @var Driver sqlite驱动 */
    protected static Driver $client;

    /** @var string 表名及数据文件名称 */
    protected static string $dbFileName = 'rate-limit';

    /** @var int 秒转为纳秒 */
    protected static int $s2ns = 1000000000;

    /** @var string sqlite数据库文件地址 */
    protected string $filename;

    /** @var string sqlite数据库文件加密秘钥 */
    protected string $encryptionKey;

    /** @var int 装满桶所需的时间 */
    protected int $seconds;
    /** @var int 桶的最大容量 */
    protected int $capacity;


    public function __construct(?array $config = null)
    {
        $config         = $config ?? (
        function_exists('config') ?
            config("plugin.workbunny.webman-rate-limiter.app", []) :
            []
        );

        $this->capacity = $config["bucket"]["capacity"] ?? 60;
        $this->seconds  = $config["bucket"]["seconds"] ?? 60;

        $this->encryptionKey = $config["sqlite"]["encryptionKey"] ?? "";
        $this->filename      = $config["sqlite"]["dbFilePath"] ?? "";


        /** 示例化SQLite客户端，并导入表结构 */
        if (!(self::$client ?? null instanceof Driver)) {
            self::$client = new Driver([
                'filename'      => $this->filename,
                'flags'         => SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
                'encryptionKey' => $this->encryptionKey
            ]);
            self::$client->create(self::$dbFileName, [
                'key'        => [
                    'VARCHAR',
                    'PRIMARY KEY',
                    'NOT NULL',
                ],
                'capacity'   => [
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
     * @param string $key
     * @param int|null $capacity
     * @param int|null $seconds
     * @return int
     * @datetime 2022/9/19 17:13
     * @author sunsgne
     */
    public function handle(string $key, ?int $capacity = null, ?int $seconds = null): int
    {
        $capacity = $capacity ?? $this->capacity;
        $seconds  = $seconds ?? $this->seconds;
        /** @var  $nowTime * 获取高精度时间 */
        $nowTime = hrtime(true);

        $res = self::$client->query('SELECT `key` , `capacity` , `updated_at` FROM `rate-limit` WHERE `key` = "' . $key . '";');

        $ipResult = [];
        if ($res instanceof SQLite3Result) {
            $ipResult = $res->fetchArray();
        }


        /** 存在此前KEY的请求 */
        if (isset($ipResult) and !empty($ipResult)) {
            if (($ipResult["updated_at"] + ($seconds * self::$s2ns)) < $nowTime) {
                /** 不在限流时间内 ,重置限流请求次数，并正常返回  */
                $this->resetBucketTime($key, $capacity - 1, $nowTime);
                return intval($capacity - 1);
            }


            $time_passed = ($nowTime - $ipResult["updated_at"]) / self::$s2ns;
            $allow       = $ipResult["capacity"];
            $allow       += $time_passed * ($capacity / $seconds);


            $capacity = min($capacity, $allow);


            if ($capacity >= 1) {
                $this->updateBucket($key, $capacity - 1, $nowTime);
                return (int)$capacity - 1;
            }
            /** 限流  */
            return 0;
        }

        /** 当前请求写入库 并正常返回 */

        $this->createBucket($key, $capacity, $nowTime);

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
    public function createBucket(string $key, int $capacity, int $time)
    {
        self::$client->insert(self::$dbFileName, [
            'key'        => $key,
            'capacity'   => $capacity,
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
    public function updateBucket(string $key, int $capacity, int $time)
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
    public function resetBucketTime(string $key, int $capacity, int $time)
    {
        self::$client->query('UPDATE "' . self::$dbFileName . '" SET `capacity` =  "' . $capacity . '" , `updated_at` = "' . $time . '" WHERE `key`= "' . $key . '";');
    }


}
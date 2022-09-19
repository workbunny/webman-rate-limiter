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


    public function __construct()
    {
        if (!(self::$client ?? null instanceof Driver)) {
            self::$client = new Driver([
                'filename'      => dirname(__DIR__) . "/src/db/" . self::$dbFileName . ".db",
                'flags'         => SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE,
                'encryptionKey' => ""
            ]);
            $this->initDB();
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
                'ip'         => [
                    'INT',
                    'PRIMARY KEY',
                    'NOT NULL',
                ],
                'request'    => [
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
                'CREATE INDEX `rate-limit-ip` ON `rate-limit` (`ip`);'
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
    public function handle(string $clientIp, int $capacity, int $seconds)
    {
        $nowTime = time();

        $res = self::$client->query('SELECT `ip` , `request` , `updated_at` FROM `rate-limit` WHERE `ip` = "' . $clientIp . '";');

        $ipResult = [];
        if ($res instanceof SQLite3Result) {
            $ipResult = $res->fetchArray();
        }


        /** 存在此前IP的请求 */
        if (isset($ipResult) and !empty($ipResult)) {
            if (($ipResult["updated_at"] + $seconds) < $nowTime) {
                /** 不在限流时间内 ,重置限流请求次数，并正常返回  */
                self::$client->query('UPDATE `rate-limit` SET `request` = 1 , `updated_at` = "' . $nowTime . '" WHERE `ip`= "' . $clientIp . '";');
                return intval($capacity - 1);
            }

            if (($ipResult["request"]++) < $capacity) {
                /** 正常返回且更新请求次数 */
                self::$client->query('UPDATE `rate-limit` SET `request` = "' . $ipResult["request"]++ . '"  WHERE `ip` = "' . $clientIp . '";');
                return $capacity - ($ipResult["request"]++);
            }
            /** 限流  */
            return 0;
        }

        /** 当前请求写入库 并正常返回 */

        self::$client->insert('rate-limit', [
            'ip'         => $clientIp,
            'request'    => 1,
            'created_at' => $nowTime,
            'updated_at' => $nowTime,
        ]);

        return intval($capacity - 1);

    }

}
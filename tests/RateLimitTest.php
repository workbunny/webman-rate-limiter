<?php


use PHPUnit\Framework\TestCase;
use Workbunny\WebmanRateLimiter\StorageClient;

class RateLimitTest extends TestCase
{


    public function testHandle()
    {
        $res = (new StorageClient())->handle("127.0.0.1" , 60 , 60);
        $this->assertEquals(59, $res);
    }
}
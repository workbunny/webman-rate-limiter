<?php


use PHPUnit\Framework\TestCase;
use Workbunny\WebmanRateLimiter\RateLimiter;

class RateLimitTest extends TestCase
{


    public function testHandle()
    {
        $res = (new RateLimiter())->handle("127.0.0.8" , 60 , 60);
        $this->assertEquals(59, $res);



    }

    public function testHandle2()
    {
        $res = (new RateLimiter())->handle("127.0.0.2" , 1 , 60);
        $this->assertEquals(0, $res);
    }

    public function testHandleRandomKey()
    {
        $res = (new RateLimiter())->handle( (string)mt_rand(9999,202220) , 60 , 60);
        $this->assertEquals(59, $res);
    }


}
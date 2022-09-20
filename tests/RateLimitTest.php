<?php


use PHPUnit\Framework\TestCase;
use Workbunny\WebmanRateLimiter\RateLimiter;

class RateLimitTest extends TestCase
{


    public function testHandle()
    {
//        dump($a = hrtime(true));
//        sleep(1);
//        dump($b =  hrtime(true));
//
//
//        dump($b-$a);
//        dump(($b-$a) / 1000000000);
        $res = (new RateLimiter())->handle("127.0.0.1" , 60 , 60);
        dump($res);
        $this->assertEquals(59, $res);



    }

    public function testHandle2()
    {
        $res = (new RateLimiter())->handle("127.0.0.2" , 1 , 60);
        dump($res);
        $this->assertEquals(0, $res);
    }


}
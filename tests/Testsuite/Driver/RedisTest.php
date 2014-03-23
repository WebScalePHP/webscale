<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use WebScale\Driver\Factory;
use WebScale\Driver\Redis;
use WebScale\Exception\InvalidArgumentException;
use Redis as ExtRedis;

class RedisTest extends AbstractDriverTest
{
    protected function getDriver()
    {
        $driver = Factory::getRedisDriver(array(), false);
        return $driver;
    }

    function testConstrunctorException1()
    {
        try {
            $redis = $this->driver->getRedis();
            $redis->setOption(ExtRedis::OPT_SERIALIZER, ExtRedis::SERIALIZER_PHP);
            new Redis($redis);
        } catch (InvalidArgumentException $e) {
            $redis->setOption(ExtRedis::OPT_SERIALIZER, ExtRedis::SERIALIZER_NONE);
            $this->assertTrue(true);
            return;
        }
        $redis->setOption(ExtRedis::OPT_SERIALIZER, ExtRedis::SERIALIZER_NONE);
        $this->assertTrue(false);
    }

    function testConstrunctorException2()
    {
        try {
            new Redis(null);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
            return;
        }
    }
}

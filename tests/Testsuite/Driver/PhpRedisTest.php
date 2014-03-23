<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use WebScale\Driver\Factory;
use WebScale\Driver\PhpRedis;
use WebScale\Exception\InvalidArgumentException;
use Redis as ExtRedis;

class PhpRedisTest extends AbstractDriverTest
{
    protected function getDriver()
    {
        $redis = Factory::getRedisDriver()->getRedis();
        $redis->setOption(ExtRedis::OPT_SERIALIZER, ExtRedis::SERIALIZER_PHP);
        return new PhpRedis($redis);
    }

    function testConstrunctorException()
    {
        try {
            $redis = $this->driver->getRedis();
            $redis->setOption(ExtRedis::OPT_SERIALIZER, ExtRedis::SERIALIZER_NONE);
            new PhpRedis($redis);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
        $redis->setOption(ExtRedis::OPT_SERIALIZER, ExtRedis::SERIALIZER_PHP);
    }
}

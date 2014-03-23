<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use PHPUnit_Framework_TestCase;
use WebScale\Driver\Factory;
use Predis\Client as Predis;
use Redis as ExtRedis;
use Memcached as ExtMemcached;
use RedisException;

class FactoryTest extends PHPUnit_Framework_TestCase
{
    public function testGetRedis()
    {
        if (extension_loaded('redis') || defined('HHVM_VERSION')) {
            $driver = Factory::getRedisDriver(array(array()));
            $redis = $driver->getRedis();
            $this->assertTrue($redis instanceof Predis);

            $driver = Factory::getRedisDriver(array(), true);
            $redis = $driver->getRedis();
            $this->assertTrue($redis instanceof Predis);
        }
    }

    public function testGetMemcached()
    {
        if (extension_loaded('memcached')) {
            $memcached = Factory::getMemcachedDriver()->getMemcached();
            $this->assertTrue(count($memcached->getServerList()) === 1);

            $memcached = Factory::getMemcachedDriver(array(array(), array()))->getMemcached();
            $this->assertTrue(count($memcached->getServerList()) === 2);
            if (ExtMemcached::HAVE_IGBINARY) {
                $expected = ExtMemcached::SERIALIZER_IGBINARY;
            } else {
                $expected = ExtMemcached::SERIALIZER_PHP;
            }
            $this->assertEquals($expected, $memcached->getOption(ExtMemcached::OPT_SERIALIZER));
            $this->assertTrue($memcached->getOption(ExtMemcached::OPT_COMPRESSION));

            $memcached = Factory::getMemcachedDriver(array(), false, false)->getMemcached();
            $this->assertEquals(ExtMemcached::SERIALIZER_PHP, $memcached->getOption(ExtMemcached::OPT_SERIALIZER));
            $this->assertFalse($memcached->getOption(ExtMemcached::OPT_COMPRESSION));

        }
    }
}

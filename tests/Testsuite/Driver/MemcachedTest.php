<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use WebScale\Driver\Factory;
use ReflectionClass;
use DateTime;

/**
 * @requires extension memcached
 */
class MemcachedTest extends AbstractDriverTest
{
    protected function getDriver()
    {
        return Factory::getMemcachedDriver();
    }

    public function testformatTtl()
    {
        $reflection = new ReflectionClass($this->driver);
        $reflectionMethod = $reflection->getMethod('formatTtl');
        $reflectionMethod->setAccessible(true);

        $res = $reflectionMethod->invoke($this->driver, 10);
        $this->assertEquals(10, $res);

        $datetimeThen = new DateTime('+60 days');
        $expected = $datetimeThen->getTimestamp();

        $datetimeNow = new DateTime();
        $ttl = $expected - $datetimeNow->getTimestamp();

        $res = $reflectionMethod->invoke($this->driver, $ttl);
        $this->assertEquals($expected, $res);
    }
}

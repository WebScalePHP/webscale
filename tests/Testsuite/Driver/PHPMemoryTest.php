<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use WebScale\Driver\PHPMemory;

class PHPMemoryTest extends AbstractDriverTest
{
    protected function getDriver()
    {
        return new PHPMemory;
    }

    public function testTtl()
    {
        $this->driver->set($this->pool, self::KEY, null, 1);
        sleep(2);
        $this->assertFalse($this->driver->exists($this->pool, self::KEY));
    }
}

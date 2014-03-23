<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use WebScale\Driver\FileSystem;

class FileSystemTest extends AbstractDriverTest
{
    protected function getDriver()
    {
        return new FileSystem(sys_get_temp_dir(). '/'. uniqid());
    }

    public function testTtl()
    {
        $this->driver->set($this->pool, self::KEY, null, 1);
        sleep(2);
        $this->assertFalse($this->driver->exists($this->pool, self::KEY));
    }

    public function testClean()
    {
        $this->driver->setMultiple($this->pool, array(
            uniqid() => null,
            uniqid() => null,
            uniqid() => null,
        ));
        $this->assertTrue($this->driver->clean(3600, 0));
        $this->assertFalse($this->driver->clean());
    }
}

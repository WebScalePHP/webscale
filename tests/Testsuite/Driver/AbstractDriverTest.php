<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use PHPUnit_Framework_TestCase;
use DateTime;
use Exception;

abstract class AbstractDriverTest extends PHPUnit_Framework_TestCase
{
    protected $driver;

    protected $pool;

    const TTL = 5;

    const KEY = 'foo';

    final protected function setUp()
    {
        try {
            $this->driver = $this->getDriver();
        } catch (Exception $e) {
            $this->markTestSkipped($e->getMessage());
        }
        $this->pool = uniqid();
    }

    abstract protected function getDriver();

    public function testString()
    {
        $data = '';
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals($data, $this->driver->get($this->pool, self::KEY, $found));
        $this->assertEquals(gettype($data), gettype($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testIntString()
    {
        $data = '42';
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals($data, $this->driver->get($this->pool, self::KEY, $found));
        $this->assertEquals(gettype($data), gettype($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testFloatString()
    {
        $data = '3.14';
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals($data, $this->driver->get($this->pool, self::KEY, $found));
        $this->assertEquals(gettype($data), gettype($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testTrue()
    {
        $data = true;
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals($data, $this->driver->get($this->pool, self::KEY, $found));
        $this->assertEquals(gettype($data), gettype($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testFalse()
    {
        $data = false;
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals($data, $this->driver->get($this->pool, self::KEY, $found));
        $this->assertEquals(gettype($data), gettype($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testNull()
    {
        $data = null;
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals($data, $this->driver->get($this->pool, self::KEY, $found));
        $this->assertEquals(gettype($data), gettype($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testInt()
    {
        $data = 42;
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals($data, $this->driver->get($this->pool, self::KEY, $found));
        $this->assertEquals(gettype($data), gettype($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testFloat()
    {
        $data = 3.14;
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals($data, $this->driver->get($this->pool, self::KEY, $found));
        $this->assertEquals(gettype($data), gettype($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testArray()
    {
        $data = array();
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals(serialize($data), serialize($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testObject()
    {
        $data = new DateTime();
        $this->assertTrue($this->driver->set($this->pool, self::KEY, $data, self::TTL));
        $this->assertEquals(serialize($data), serialize($this->driver->get($this->pool, self::KEY, $found)));
        $this->assertTrue($found);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
    }

    public function testClear()
    {
        $this->driver->set($this->pool, self::KEY, null, self::TTL);
        $this->driver->clearNamespace($this->pool);
        $this->assertFalse($this->driver->exists($this->pool, self::KEY));
    }

    public function testDelete()
    {
        $this->driver->set($this->pool, self::KEY, null);
        $this->assertTrue($this->driver->delete($this->pool, self::KEY));
        $this->assertFalse($this->driver->exists($this->pool, self::KEY));
    }

    public function testDeleteNonExisting()
    {
        $this->assertTrue($this->driver->delete($this->pool, 'notfound'));
    }

    public function testSetMultiple()
    {
        $keys = array(
            uniqid() => null,
        );
        $this->assertTrue($this->driver->setMultiple($this->pool, $keys, self::TTL));
    }

    public function testGetMultiple()
    {
        $key = uniqid();
        $key2 = uniqid();
        $this->driver->setMultiple($this->pool, array($key => false, $key2 => ''), self::TTL);
        $values = $this->driver->getMultiple($this->pool, array($key, $key2, 'notfound'));
        $this->assertTrue(count($values) === 2);
    }

    public function testDeleteMultiple()
    {
        $key = uniqid();
        $this->driver->setMultiple($this->pool, array($key => null));
        $this->assertTrue($this->driver->deleteMultiple($this->pool, array($key)));
        $this->assertFalse($this->driver->exists($this->pool, $key));
    }

    public function testIsolation()
    {
        $this->driver->set($this->pool, self::KEY, null, self::TTL);
        $subpool = $this->pool . ':' . uniqid();
        $this->assertFalse($this->driver->exists($subpool, self::KEY));
        $this->driver->set($subpool, self::KEY, null, self::TTL);
        $this->assertTrue($this->driver->exists($subpool, self::KEY));
        $this->driver->clearNamespace($subpool);
        $this->assertTrue($this->driver->exists($this->pool, self::KEY));
        $this->assertFalse($this->driver->exists($subpool, self::KEY));
    }

    public function testNotFound()
    {
        $this->assertNull($this->driver->get($this->pool, 'notfound', $found));
        $this->assertFalse($found);
        $this->assertFalse($this->driver->exists($this->pool, 'notfound'));
    }
}

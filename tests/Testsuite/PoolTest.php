<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests;

use WebScale\Pool;
use WebScale\Item\Item;
use WebScale\Item\ItemInterface;
use WebScale\Item\ItemCollection;
use WebScale\Driver\PHPMemory;
use PHPUnit_Framework_TestCase;

class PoolTest extends PHPUnit_Framework_TestCase
{
    private $pool;

    protected function setUp()
    {
        $this->pool = new Pool(new PHPMemory);
    }

    public function testGetItem()
    {
        $this->assertTrue($this->pool->getItem('foo') instanceof ItemInterface);
    }

    public function testGetItems()
    {
        $this->assertTrue($this->pool->getItems(array('foo', 'bar')) instanceof ItemCollection);
    }

    public function testClear()
    {
        $this->pool->getItem('foo')->set('bar');
        $this->assertTrue($this->pool->getItem('foo')->isHit());
        $this->assertTrue($this->pool->clear() instanceof Pool);
        $this->assertFalse($this->pool->getItem('foo')->isHit());
    }

    public function testSubPool()
    {
        $subpool = $this->pool->getSubPool('sub');

        $subpool->getItem('foo')->set(null);

        $this->assertTrue($subpool->getItem('foo')->isHit());

        $this->pool->clear();

        $this->assertFalse($subpool->getItem('foo')->isHit());
    }
}

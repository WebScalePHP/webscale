<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Item;

use WebScale\Pool;
use WebScale\Driver\PHPMemory;
use PHPUnit_Framework_TestCase;

class PipeTest extends PHPUnit_Framework_TestCase
{
    private $pool;

    protected function setUp()
    {
        $this->pool = new Pool(new PHPMemory);
    }

    public function testPipe()
    {
        $res = $this->pool->getItems(array('foo', 'bar'))->pipe(function ($items) {
            foreach ($items as $item) {
                $item->set($item->getKey());
            }
        });
        $this->assertEquals($res['foo'], 'foo');
        $this->assertEquals($res['bar'], 'bar');

        $this->assertTrue($this->pool->getItem('foo')->isHit());
    }

    public function testClear()
    {
        $this->pool->getItem('baz')->set(true);
        $this->pool->getItem('quz')->set(true);

        $this->assertTrue($this->pool->getItem('baz')->isHit());
        $this->assertTrue($this->pool->getItem('quz')->isHit());

        $this->pool->getItems(array('baz', 'quz'))->clear();

        $this->assertFalse($this->pool->getItem('baz')->isHit());
        $this->assertFalse($this->pool->getItem('quz')->isHit());
    }
}

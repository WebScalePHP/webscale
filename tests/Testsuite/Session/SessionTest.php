<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Session;

use WebScale\Exception\InvalidArgumentException;
use WebScale\Driver\PHPMemory;
use WebScale\Session\Handler;
use PHPUnit_Framework_TestCase;

class SessionTest extends PHPUnit_Framework_TestCase
{
    private $handler;

    protected function setUp()
    {
        $this->handler = new Handler(new PHPMemory);
    }

    public function testRegister()
    {
        $this->assertTrue($this->handler->register());
    }

    public function testWrite()
    {
        $id = uniqid();
        $data = uniqid();
        $this->assertTrue($this->handler->write($id, $data));
        $this->assertEquals($data, $this->handler->read($id));
    }

    public function testDestroy()
    {
        $id = uniqid();
        $this->handler->write($id, 'foobar');
        $this->assertTrue($this->handler->destroy($id));
        $this->assertEquals('', $this->handler->read($id));
    }

    public function testEmpty()
    {
        $this->assertEquals('', $this->handler->read(uniqid()));
    }

    public function testException()
    {
        try {
            new Handler(new PHPMemory, null);
            $this->assertTrue(false);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}

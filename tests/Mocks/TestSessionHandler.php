<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Mocks;

use ArrayAccess;
use SessionHandlerInterface;

class TestSessionHandler implements SessionHandlerInterface
{
    protected $data;

    public function __construct(ArrayAccess $store)
    {
        $this->data = $store;
    }

    public function read($session_id)
    {
        if (isset($this->data[$session_id])) {
            return $this->data[$session_id];
        }
        return '';
    }

    public function write($session_id, $session_data)
    {
        $this->data[$session_id] = $session_data;
        return true;
    }

    public function destroy($session_id)
    {
        if (isset($this->data[$session_id])) {
            unset($this->data[$session_id]);
        }
        return true;
    }

    public function open($save_path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function gc($maxlifetime)
    {
        return true;
    }
}

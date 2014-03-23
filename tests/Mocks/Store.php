<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Mocks;

class Store
{
    const EXPIRES = 0;

    const DATA = 1;

    protected $data = array();

    protected static $instances = array();

    private function __construct()
    {
    }

    public static function getInstance($name)
    {
        if (!isset(static::$instances[$name])) {
            static::$instances[$name] = new self();
        }
        return static::$instances[$name];
    }

    public function get($key, &$found = null)
    {
        if ($this->exists($key)) {
            $found = true;
            return $this->data[$key][self::DATA];
        }
        $found = false;
        return false;
    }

    public function set($key, $value, $ttl = 0)
    {
        if ($ttl == 0 || is_null($ttl)) {
            $expires = null;
        } else {
            $expires = time() + $ttl;
        }
        $this->data[$key] = array(
            self::EXPIRES    => $expires,
            self::DATA         => $value
        );
        return true;
    }

    public function add($key, $value, $ttl = 0)
    {
        if ($this->exists($key) === false) {
            return $this->set($key, $value, $ttl);
        }
        return false;
    }

    public function delete($key)
    {
        $result = $this->exists($key);
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
        return $result;
    }

    public function exists($key)
    {
        if (!isset($this->data[$key])) {
            return false;
        }
        if ($this->data[$key][self::EXPIRES] === null || $this->data[$key][self::EXPIRES] > time()) {
            return true;
        }
        return false;
    }

    public function clear()
    {
        $this->data = array();
    }
}

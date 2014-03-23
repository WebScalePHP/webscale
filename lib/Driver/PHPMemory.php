<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

/**
 * Cache driver for PHP memory.
 */
class PHPMemory extends AbstractDriver
{
    const EXPIRES = 0;

    const DATA = 1;

    protected $data = array();

    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        if ($this->doExists($key)) {
            $found = true;
            return $this->data[$key][self::DATA];
        }
        $found = false;
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        $expires = is_null($ttl) ? 0 : time() + $ttl;
        $this->data[$key] = array(
            self::EXPIRES    => $expires,
            self::DATA         => $value,
        );
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        if (!isset($this->data[$key])) {
            return false;
        }
        if ($this->data[$key][self::EXPIRES] === 0 || $this->data[$key][self::EXPIRES] > time()) {
            return true;
        }
        return false;
    }

    /********************************************************************************
     * \WebScale\Driver\DriverInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function isAvailable()
    {
        return true;
    }
}

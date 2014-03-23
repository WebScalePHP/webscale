<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Exception\RuntimeException;
use WebScale\Driver\Helper\ValueWrapper;
use Memcache as ExtMemcache;

// @codeCoverageIgnoreStart
if (!defined('MEMCACHE_COMPRESSED')) {
    define('MEMCACHE_COMPRESSED', 0); // hhvm
}
// @codeCoverageIgnoreEnd

/**
 * Cache driver for Memcache.
 */
class Memcache extends AbstractMemcachedDriver
{
    /**
     * @ignore
     */
    protected $flag;

    /**
     * @ignore
     */
    protected $memcache;

    /**
     * Constructor
     *
     * @param \Memcache $memcache
     */
    public function __construct(ExtMemcache $memcache, $flag = MEMCACHE_COMPRESSED)
    {
        $this->memcache = $memcache;
        $this->flag = $flag;
    }

    /**
     * Get the Memcache instance used by the driver.
     *
     * @return \Memcache
     * @codeCoverageIgnore
     */
    public function getMemcache()
    {
        return $this->memcache;
    }

    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        if (false !== ($value = $this->memcache->get($key))) {
            $found = true;
            if ($value instanceof ValueWrapper) {
                $value = $value->get();
            }
            return $value;
        }
        $found = false;
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        if ($value === false || is_integer($value) || is_float($value) || defined('HHVM_VERSION')) {
            $value = new ValueWrapper($value);
        }
        return $this->memcache->set($key, $value, $this->flag, $this->formatTtl($ttl));
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        $this->memcache->delete($key);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        return (false !== $this->memcache->get($key));
    }

    /********************************************************************************
     * \WebScale\Driver\DriverInterface
     *******************************************************************************/

    public static function isAvailable()
    {
        return extension_loaded('memcache');
    }
}

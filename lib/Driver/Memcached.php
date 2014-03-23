<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Driver\Helper\ValueWrapper;
use Memcached as ExtMemcached;

/**
 * Cache driver for Memcached.
 */
class Memcached extends AbstractMemcachedDriver
{
    /**
     * @ignore
     */
    protected $memcached;

    /**
     * Constructor
     *
     * @param \Memcached $memcached
     */
    public function __construct(ExtMemcached $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     * Get the Memcached instance used by the driver
     *
     * @return \Memcached
     * @codeCoverageIgnore
     */
    public function getMemcached()
    {
        return $this->memcached;
    }

    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        $value = $this->memcached->get($key);
        if (false !== $value || $this->memcached->getResultCode() === ExtMemcached::RES_SUCCESS) {
            $found = true;
            return $value;
        }
        $found = false;
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetMultiple(array $keys)
    {
        return $this->memcached->getMulti($keys);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        return $this->memcached->set($key, $value, $this->formatTtl($ttl));
    }

    /**
     * {@inheritdoc}
     */
    protected function doSetMultiple(array $keys, $ttl = null)
    {
        return $this->memcached->setMulti($keys, $this->formatTtl($ttl));
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        $this->memcached->delete($key);
        if (in_array($this->memcached->getResultCode(), array(ExtMemcached::RES_NOTFOUND, ExtMemcached::RES_SUCCESS))) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteMultiple(array $keys)
    {
        if (defined('HHVM_VERSION')) {
            // @codeCoverageIgnoreStart
            return parent::doDeleteMultiple($keys);
            // @codeCoverageIgnoreEnd
        }
        $this->memcached->deleteMulti($keys);
        if (in_array($this->memcached->getResultCode(), array(ExtMemcached::RES_NOTFOUND, ExtMemcached::RES_SUCCESS))) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        $this->memcached->get($key);
        if ($this->memcached->getResultCode() === ExtMemcached::RES_SUCCESS) {
            return true;
        }
        return false;
    }

    /********************************************************************************
     * \WebScale\Driver\DriverInterface
     *******************************************************************************/

    public static function isAvailable()
    {
        return extension_loaded('memcached');
    }
}

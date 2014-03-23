<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Exception\RuntimeException;

/**
 * Cache driver for WinCache.
 */
class WinCache extends AbstractDriver
{
    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        return wincache_ucache_get($key, $found);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetMultiple(array $keys)
    {
        return wincache_ucache_get($keys);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        return wincache_ucache_set($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSetMultiple(array $keys, $ttl = null)
    {
        wincache_ucache_set($keys, null, $ttl);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        wincache_ucache_delete($key);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        return wincache_ucache_exists($key);
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
        return (extension_loaded('wincache') && ini_get('wincache.ucenabled'))
                    && ((php_sapi_name() !== 'cli') || ini_get('wincache.enablecli'));
    }
}

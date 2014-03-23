<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Exception\RuntimeException;

/**
 * Cache driver for Apc.
 */
class Apc extends AbstractDriver
{
    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/
 
    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        return apc_fetch($key, $found);
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetMultiple(array $keys)
    {
        return apc_fetch($keys);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        return apc_store($key, $value, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSetMultiple(array $keys, $ttl = null)
    {
        apc_store($keys, null, $ttl);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        apc_delete($key);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        return apc_exists($key);
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
        return (extension_loaded('apc') && ini_get('apc.enabled'))
                    && ((php_sapi_name() !== 'cli') || ini_get('apc.enable_cli'));
    }
}

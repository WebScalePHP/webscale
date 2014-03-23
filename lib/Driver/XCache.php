<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Serializer\SerializerInterface;
use WebScale\Exception\RuntimeException;

/**
 * Cache driver for XCache.
 */
class XCache extends AbstractDriver
{
    /**
     * @ignore
     */
    protected $serializer;

    public function __construct(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer ? $serializer : Factory::getSerializer(false, true);
    }

    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        $value = xcache_get($key);
        if (is_string($value)) {
            $found = true;
            return $this->serializer->unserialize($value);
        }
        $found = false;
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        return xcache_set($key, $this->serializer->serialize($value), $ttl);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        xcache_unset($key);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        return xcache_isset($key);
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
        return (extension_loaded('xcache') && (php_sapi_name() !== 'cli'));
    }
}

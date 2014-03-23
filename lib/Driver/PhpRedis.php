<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Common\SerializerInterface;
use WebScale\Exception\InvalidArgumentException;
use WebScale\Driver\Helper\ValueWrapper;
use Redis as ExtRedis;

/**
 * Cache driver for Phpredis extension.
 */
class PhpRedis extends Redis
{
    /**
     * @ignore
     */
    protected $redis;

    /**
     * Construnctor
     *
     * @param \Redis|\Predis\Client $redis
     * @param array $options
     */
    public function __construct(ExtRedis $redis)
    {
        if ($redis->getOption(ExtRedis::OPT_SERIALIZER) === ExtRedis::SERIALIZER_NONE) {
            throw new InvalidArgumentException(
                "If you dont't want to use phpredis extension's built in serializer "
                ."choose WebScale\\Driver\\Redis instead."
            );
        }
        $this->redis = $redis;
    }

    /**
     * Get the Redis instance used by the driver
     *
     * @return \Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        if (false !== ($value = $this->redis->get($key))) {
            $found = true;
            if ($value instanceof ValueWrapper) {
                return $value->get();
            }
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
        $out = array();
        $tmp = $this->redis->mGet($keys);
        foreach ($keys as $index => $key) {
            if ($tmp[$index] !== false) {
                if ($tmp[$index] instanceof ValueWrapper) {
                    $out[$key] = $tmp[$index]->get();
                } else {
                    $out[$key] = $tmp[$index];
                }
            }
        }
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        if ($value === false || (is_null($value) && defined('HHVM_VERSION'))) {
            $value = new ValueWrapper($value);
        }
        if ($ttl) {
            return $this->redis->setex($key, $ttl, $value);
        }
        return $this->redis->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSetMultiple(array $keys, $ttl = null)
    {
        $keys = array_map(function ($value) {
            if ($value === false || (is_null($value) && defined('HHVM_VERSION'))) {
                return new ValueWrapper($value);
            }
            return $value;
        }, $keys);

        $this->redis->multi();
        foreach ($keys as $key => &$value) {
            if ($ttl) {
                $this->redis->setex($key, $ttl, $value);
            } else {
                $this->redis->set($key, $value);
            }
        }
        $this->redis->exec();
        return true;
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
        return (extension_loaded('redis') || defined('HHVM_VERSION'));
    }
}

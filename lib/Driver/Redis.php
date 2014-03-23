<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Serializer\SerializerInterface;
use WebScale\Exception\InvalidArgumentException;
use Predis\Client as Predis;
use Redis as ExtRedis;
use Predis\Connection\AggregatedConnectionInterface;
use Predis\Connection\SingleConnectionInterface;

/**
 * Cache driver for Predis library or Phpredis extension.
 */
class Redis extends AbstractDriver
{
    /**
     * @ignore
     */
    protected $redis;

    /**
     * @ignore
     */
    protected $serializer;

    /**
     * Construnctor
     *
     * @param \Redis|\Predis\Client $redis
     * @param array $options
     */
    public function __construct($redis, SerializerInterface $serializer = null)
    {
        if ($redis instanceof Predis) {
            $this->redis = $redis;
        } elseif ($redis instanceof ExtRedis) {
            if ($redis->getOption(ExtRedis::OPT_SERIALIZER) !== ExtRedis::SERIALIZER_NONE) {
                throw new InvalidArgumentException(
                    "If you want to use phpredis extension's built in serializer "
                    ."choose WebScale\\Driver\\PhpRedis instead."
                );
            }
            $this->redis = $redis;
        } else {
            throw new InvalidArgumentException(
                '$redis must be instance of \Redis or \Predis\Client'
            );
        }
        $this->serializer = $serializer ? $serializer : Factory::getSerializer(false, true);
    }

    /**
     * Get the Redis instance used by the driver
     *
     * @return \Redis|\Predis\Client
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
        $value = $this->redis->get($key);
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
    protected function doGetMultiple(array $keys)
    {
        $out = array();
        $tmp = $this->mGet($keys);
        foreach ($keys as $index => $key) {
            if (is_string($tmp[$index])) {
                $out[$key] = $this->serializer->unserialize($tmp[$index]);
            }
        }
        return $out;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        if ($ttl) {
            return $this->redis->setex($key, $ttl, $this->serializer->serialize($value));
        }
        return $this->redis->set($key, $this->serializer->serialize($value));
    }

    /**
     * {@inheritdoc}
     */
    protected function doSetMultiple(array $keys, $ttl = null)
    {
        $serializer = $this->serializer;
        $keys = array_map(function ($value) use ($serializer) {
            return $serializer->serialize($value);
        }, $keys);

        $context = $this->getMultiContext();
        foreach ($keys as $key => &$value) {
            if ($ttl) {
                $context->setex($key, $ttl, $value);
            } else {
                $context->set($key, $value);
            }
        }
        $this->execMultiContext($context);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        $this->redis->del($key);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDeleteMultiple(array $keys)
    {
        $context = $this->getMultiContext();
        foreach ($keys as $key) {
            $context->del($key);
        }
        $this->execMultiContext($context);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        return $this->redis->exists($key);
    }

    /********************************************************************************
     * \WebScale\Driver\DriverInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public static function isAvailable()
    {
        return (extension_loaded('redis') || defined('HHVM_VERSION') || class_exists('Predis\Client'));
    }

    /********************************************************************************
     * Protected
     *******************************************************************************/

    protected function getMultiContext()
    {
        if ($this->redis instanceof ExtRedis) {
            return $this->redis->multi();
        }
        if ($this->redis->getConnection() instanceof AggregatedConnectionInterface) {
            return $this->redis->pipeline();
        }
        return $this->redis->transaction();
    }

    protected function execMultiContext($context)
    {
        if ($this->redis instanceof ExtRedis) {
            return $context->exec();
        }
        return $context->execute();
    }

    protected function mGet(array $keys)
    {
        if ($this->redis instanceof ExtRedis || $this->redis->getConnection() instanceof SingleConnectionInterface) {
            return $this->redis->mGet($keys);
        }
        return $this->redis->pipeline(function ($pipe) use ($keys) {
            foreach ($keys as $key) {
                $pipe->get($key);
            }
        });
    }
}

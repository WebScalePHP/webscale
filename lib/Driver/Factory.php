<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Serializer\Compressor as CompressingSerializer;
use WebScale\Serializer\Igbinary as IgbinarySerializer;
use WebScale\Serializer\Native as PHPSerializer;

use WebScale\Exception\RuntimeException;
use Memcached as ExtMemcached;
use Couchbase as ExtCouchbase;
use Memcache as ExtMemcache;
use Predis\Client as Predis;
use Redis as ExtRedis;

class Factory
{
    protected static $default_redis = array(
        'host'                     => 'localhost',
        'port'                     => 6379,
        'timeout'                => 0.5, // seconds
        'retry_interval'       => 100, // milliseconds
        'db'                       => 0,
    );

    protected static $default_memcache = array(
        'host'                    => 'localhost',
        'port'                    => 11211,
        'persistent'            => true,
        'weight'                 => 1,
        'timeout'               => 1,
        'retry_interval'       => null,
        'status'                  => null,
        'failure_callback'    => null,
    );

    protected static $default_couchbase = array(
        'host'                    => 'localhost',
        'port'                    => 11211,
        'user'                    => null,
        'password'             => null,
        'bucket'                 => 'beer-sample',
        'persistent'            => true
    );

    /**
     * @codeCoverageIgnore
     */
    public static function getUserCacheDriver()
    {
        if (Apc::isAvailable()) {
            return new Apc;
        }
        if (WinCache::isAvailable()) {
            return new WinCache;
        }
        if (XCache::isAvailable()) {
            return new XCache;
        }
        throw new RuntimeException('No user cache extension available');
    }

    public static function getRedisDriver(
        array $connection = array(),
        $force_predis = false
    ) {
        if (Redis::isAvailable() === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                'Redis extension or Predis library required'
            );
            // @codeCoverageIgnoreEnd
        }
        if ((extension_loaded('redis') || defined('HHVM_VERSION'))
            && !is_array(reset($connection))
            && !$force_predis
        ) {
            $wrapped = new ExtRedis;
            $connection = array_merge(static::$default_redis, $connection);
            if (isset($connection['path'])) {
                $wrapped->connect($connection['path']);
            } else {
                $wrapped->connect($connection['host'], $connection['port'], $connection['timeout'], null);
            }
            $wrapped->select($connection['db']);
            $wrapped->setOption(ExtRedis::OPT_SERIALIZER, ExtRedis::SERIALIZER_NONE);
            return new Redis($wrapped);
        } else {
            $connectionClass = extension_loaded('phpiredis')
                ?
                'Predis\Connection\PhpiredisStreamConnection'
                :
                'Predis\Connection\StreamConnection'
            ;
            $options = array(
                'connections'    => array(
                    'tcp'    => $connectionClass,
                    'unix'  => $connectionClass,
                ),
            );
            if (!is_array(reset($connection))) {
                $connection = array_merge(static::$default_redis, $connection);
            } else {
                foreach ($connection as &$server) {
                    $server = array_merge(static::$default_redis, $server);
                }
            }
            return new Redis(new Predis($connection, $options));
        }
    }

    public static function getMemcacheDriver(
        array $servers = array(),
        $compression = true
    ) {
        if (Memcache::isAvailable() === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                'Memcache extension required'
            );
            // @codeCoverageIgnoreEnd
        }
        $memcache = new ExtMemcache;
        if (is_array(reset($servers))) {
            // already tested on getRedisDriver and getMemcachedDriver methods
            // @codeCoverageIgnoreStart
            foreach ($servers as $server) {
                $server = array_merge($server, static::$default_memcache);
                $memcache->addServer(
                    $server['host'],
                    $server['port'],
                    $server['persistent'],
                    $server['weight'],
                    $server['timeout'],
                    $server['retry_interval'],
                    $server['status'],
                    $server['failure_callback']
                );
            }
        } else {
            // @codeCoverageIgnoreEnd
            $server = array_merge($servers, static::$default_memcache);
            $memcache->addServer(
                $server['host'],
                $server['port'],
                $server['persistent'],
                $server['weight'],
                $server['timeout'],
                $server['retry_interval'],
                $server['status'],
                $server['failure_callback']
            );
        }
        $flag = $compression ? MEMCACHE_COMPRESSED : 0;
        return new Memcache($memcache, $flag);
    }

    public static function getMemcachedDriver(
        array $servers = array(),
        $compression = true,
        $allowIgBinary = true,
        $username = null,
        $password = null
    ) {
        if (Memcached::isAvailable() === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                'Memcached extension required'
            );
            // @codeCoverageIgnoreEnd
        }
        $memcached = new ExtMemcached;
        $memcached->setOption(ExtMemcached::OPT_COMPRESSION, $compression);
        if ($allowIgBinary) {
            $memcached->setOption(
                ExtMemcached::OPT_SERIALIZER,
                $memcached->getOption(ExtMemcached::HAVE_IGBINARY) ?
                ExtMemcached::SERIALIZER_IGBINARY : ExtMemcached::SERIALIZER_PHP
            );
        } else {
            $memcached->setOption(ExtMemcached::OPT_SERIALIZER, ExtMemcached::SERIALIZER_PHP);
        }

        if (is_array(reset($servers))) {
            foreach ($servers as $server) {
                $server = array_merge($server, static::$default_memcache);
                $memcached->addServer($server['host'], $server['port'], $server['weight']);
            }
        } else {
            $server = array_merge($servers, static::$default_memcache);
            $memcached->addServer($server['host'], $server['port'], $server['weight']);
        }
        if ($username && $password) {
            $memcached->setSaslAuthData($username, $password);
        }
        return new Memcached($memcached);
    }

    public static function getCouchBaseDriver(
        array $server = array()
    ) {
        if (Couchbase::isAvailable() === false) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(
                'Couchbase extension required'
            );
            // @codeCoverageIgnoreEnd
        }
        $server = array_merge($server, static::$default_couchbase);
        return new Couchbase(
            new ExtCouchbase(
                $server['host'] . ':' . $server['port'],
                $server['user'],
                $server['password'],
                $server['bucket'],
                $server['persistent']
            )
        );
    }

    public static function getSerializer($portable = false, $compression = true)
    {
        $serializer = $portable || !extension_loaded('igbinary')
            ?
            new PHPSerializer
            :
            new IgbinarySerializer
        ;
        return $compression
            ?
            new CompressingSerializer($serializer)
            :
            $serializer
        ;
    }
}

<?php
/**
 * This file is part of the WebScale Serializer library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace
{
    $loader = false;

    foreach (array(
        dirname(__DIR__). '/vendor/autoload.php',
        dirname(dirname(__DIR__)). '/vendor/autoload.php',
        dirname(dirname(__DIR__)). '/autoload.php',
        dirname(dirname(dirname(__DIR__))). '/autoload.php',
    ) as $file) {
        if (file_exists($file)) {
            $loader = require $file;
            break;
        }
    }

    if (!$loader) {
        throw new RuntimeException("Cannot find Composer's autoloader");
    }

    $loader->addPsr4('WebScale\\Mocks\\', __DIR__. '/Mocks');

    define('WEBSCALE_TEST', true);

    require __DIR__. '/Mocks/apc.php';
    require __DIR__. '/Mocks/wincache.php';
    require __DIR__. '/Mocks/xcache.php';

    // prevent warnings about missing default timezone
    date_default_timezone_set('Europe/Helsinki');

    if (defined('REDIS_PORT')) {
        $reflection = new ReflectionProperty('WebScale\Driver\Factory', 'default_redis');
        $reflection->setAccessible(true);
        $value = $reflection->getValue();
        $value['port'] = REDIS_PORT;
        $reflection->setValue($value);
    }

    if (defined('COUCHBASE_PORT')) {
        $reflection = new ReflectionProperty('WebScale\Driver\Factory', 'default_couchbase');
        $reflection->setAccessible(true);
        $value = $reflection->getValue();
        $value['port'] = COUCHBASE_PORT;
        $reflection->setValue($value);
    }

    if (defined('MEMCACHE_PORT')) {
        $reflection = new ReflectionProperty('WebScale\Driver\Factory', 'default_memcache');
        $reflection->setAccessible(true);
        $value = $reflection->getValue();
        $value['port'] = MEMCACHE_PORT;
        $reflection->setValue($value);
    }
}

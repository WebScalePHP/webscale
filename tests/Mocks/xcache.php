<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Mocks\Store;

const XC_TYPE_PHP = 0;
const XC_TYPE_VAR = 1;

function xcache_get($key)
{
    if (is_array($key)) {
        throw new InvalidArgumentException(
            'XCache cannot fetch multiple items at once'
        );
    }
    $value = Store::getInstance('xcache')->get($key, $success);
    if ($success) {
        return $value;
    }
    return null;
}

function xcache_set($key, $value, $ttl = 0)
{
    if (is_array($value) || is_object($value)) {
        throw new InvalidArgumentException(
            'XCache cannot store objects or arrays'
        );
    }
    return Store::getInstance('xcache')->set($key, $value, $ttl);
}

function xcache_inc($key, $step = 1, $ttl = 0)
{
    $step = (int) $step;
    $current = xcache_get($key);
    $new = $current + $step;
    xcache_set($key, $new, $ttl);
    return $new;
}

function xcache_dec($key, $step = 1, $ttl = 0)
{
    $step = (int) $step;
    $current = xcache_get($key);
    $new = $current - $step;
    xcache_set($key, $new, $ttl);
    return $new;
}

function xcache_isset($key)
{
    return Store::getInstance('xcache')->exists($key);
}

function xcache_unset($key)
{
    return Store::getInstance('xcache')->delete($key);
}

function xcache_clear_cache($type, $id = null)
{
    if ($id === XC_TYPE_VAR) {
        return Store::getInstance('xcache')->clear();
    }
    return false;
}

<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Mocks\Store;

function wincache_ucache_get($key, &$success = null)
{
    if (!is_array($key)) {
        return Store::getInstance('wincache')->get($key, $success);
    }
    $out = array();
    foreach ($key as $key) {
        $value = Store::getInstance('wincache')->get($key, $success);
        if ($success) {
            $out[$key] = $value;
        }
    }
    $success = true;
    return $out;
}

function wincache_ucache_set($key, $value = null, $ttl = 0)
{
    if (!is_array($key)) {
        return Store::getInstance('wincache')->set($key, $value, $ttl);
    } else {
        foreach ($key as $key => $value) {
            Store::getInstance('wincache')->set($key, $value, $ttl);
        }
        return array();
    }
}

function wincache_ucache_add($key, $value = null, $ttl = 0)
{
    if (!is_array($key)) {
        return Store::getInstance('wincache')->add($key, $value, $ttl);
    } else {
        $out = array();
        foreach ($key as $key => $value) {
            if (Store::getInstance('wincache')->add($key, $value, $ttl) === false) {
                $out[$key] = -1;
            }
        }
        return $out;
    }
}

function wincache_ucache_inc($key, $step = 1, &$success = null)
{
    $step = (int) $step;
    $current = Store::getInstance('wincache')->get($key, $found);
    if ($found && is_integer($current)) {
        $new = $current + $step;
        Store::getInstance('wincache')->set($key, $new);
        $success = true;
        return $new;
    } else {
        $success = false;
        return false;
    }
}

function wincache_ucache_dec($key, $step = 1, &$success = null)
{
    $step = (int) $step;
    $current = Store::getInstance('wincache')->get($key, $found);
    if ($found && is_integer($current)) {
        $new = $current - $step;
        Store::getInstance('wincache')->set($key, $new);
        $success = true;
        return $new;
    } else {
        $success = false;
        return false;
    }
}

function wincache_ucache_exists($key)
{
    return Store::getInstance('wincache')->exists($key);
}

function wincache_ucache_delete($key)
{
    return Store::getInstance('wincache')->delete($key);
}

function wincache_ucache_clear($type = null)
{
    return Store::getInstance('wincache')->clear();
}

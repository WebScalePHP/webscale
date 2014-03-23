<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Mocks\Store;

function apc_fetch($key, &$success = null)
{
    if (!is_array($key)) {
        return Store::getInstance('apc')->get($key, $success);
    }
    $out = array();
    foreach ($key as $key) {
        $value = Store::getInstance('apc')->get($key, $success);
        if ($success) {
            $out[$key] = $value;
        }
    }
    $success = true;
    return $out;
}

function apc_store($key, $value = null, $ttl = 0)
{
    if (!is_array($key)) {
        return Store::getInstance('apc')->set($key, $value, $ttl);
    } else {
        foreach ($key as $key => $value) {
            Store::getInstance('apc')->set($key, $value, $ttl);
        }
        return array();
    }
}

function apc_add($key, $value = null, $ttl = 0)
{
    if (!is_array($key)) {
        return Store::getInstance('apc')->add($key, $value, $ttl);
    } else {
        $out = array();
        foreach ($key as $key => $value) {
            if (Store::getInstance('apc')->add($key, $value, $ttl) === false) {
                $out[$key] = -1; // question for Apc developers: WHAT THE FUCK DOES THIS MEAN?
            }
        }
        return $out;
    }
}

function apc_inc($key, $step = 1, &$success = null)
{
    $step = (int) $step;
    $current = Store::getInstance('apc')->get($key, $found);
    if ($found && is_integer($current)) {
        $new = $current + $step;
        Store::getInstance('apc')->set($key, $new);
        $success = true;
        return $new;
    } else {
        $success = false;
        return false;
    }
}

function apc_dec($key, $step = 1, &$success = null)
{
    $step = (int) $step;
    $current = Store::getInstance('apc')->get($key, $found);
    if ($found && is_integer($current)) {
        $new = $current - $step;
        Store::getInstance('apc')->set($key, $new);
        $success = true;
        return $new;
    } else {
        $success = false;
        return false;
    }
}

function apc_exists($key)
{
    return Store::getInstance('apc')->exists($key);
}

function apc_delete($key)
{
    return Store::getInstance('apc')->delete($key);
}

function apc_clear_cache($type = null)
{
    return Store::getInstance('apc')->clear();
}

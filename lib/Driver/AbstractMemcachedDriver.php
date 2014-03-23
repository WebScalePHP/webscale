<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

abstract class AbstractMemcachedDriver extends AbstractDriver
{
    protected function formatTtl($ttl)
    {
        if ($ttl > 30 * 24 * 3600) {
            $ttl = time() + $ttl;
        }
        return $ttl;
    }
}

<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

interface DriverInterface
{
    /**
     * @ignore
     */
    const ERRMODE_EXCEPTION = 0;

    /**
     * @ignore
     */
    const ERRMODE_WARNING = 1;

    /**
     * @ignore
     */
    const ERRMODE_SILENT = 2;

    public function setErrorMode($mode);

    public function getErrorMode();

    public function get($namespace, $key, &$found = null);

    public function getMultiple($namespace, array $keys);

    public function set($namespace, $key, $value, $ttl = null);

    public function setMultiple($namespace, array $keys, $ttl = null);

    public function delete($namespace, $key);

    public function deleteMultiple($namespace, array $keys);

    public function exists($namespace, $key);

    public function clearNamespace($namespace);

    public static function isAvailable();
}

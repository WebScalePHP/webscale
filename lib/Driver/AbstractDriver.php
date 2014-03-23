<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Exception\UnexpectedValueException;
use WebScale\Exception\InvalidArgumentException;
use WebScale\Exception\RuntimeException;
use WebScale\Driver\Helper\Util;
use WebScale\Version;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Exception;

// @codeCoverageIgnoreStart
if (!defined('WEBSCALE_TEST')) {
    define('WEBSCALE_TEST', false);
}
// @codeCoverageIgnoreEnd

/**
 * Abstract cache driver.
 */
abstract class AbstractDriver implements DriverInterface, LoggerAwareInterface
{
    /**
     * @ignore
     */
    private $namespaces = array();

    /**
     * @ignore
     */
    private $logger;

    /**
     * @ignore
     */
    protected $error = false;

    /**
     * @ignore
     */
    protected $errmode = DriverInterface::ERRMODE_WARNING;

    /********************************************************************************
     * \WebScale\Driver\DriverInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    final public function get($namespace, $key, &$found = null)
    {
        if ($this->error) {
            $found = false;
            return null;
        }
        try {
            if ($namespace) {
                $key = $this->getStoreKey($namespace, $key);
            }
            $data = $this->doGet($key, $found);
            if ($found) {
                return $data;
            }
            return null;
        } catch (Exception $e) {
            $this->critical($e);
            $found = false;
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function getMultiple($namespace, array $keys)
    {
        if ($this->error) {
            return array();
        }
        try {
            if ($namespace) {
                $data = $this->doGetMultiple($this->getStoreKeys($namespace, $keys));
                $out = array();
                $base = $this->getStoreKey($namespace, '');
                $baseLen = strlen($base);
                foreach ($data as $key => &$value) {
                    $out[substr($key, $baseLen)] = $value;
                }
                return $out;
            }
            return $this->doGetMultiple($keys);
        } catch (Exception $e) {
            $this->critical($e);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function set($namespace, $key, $value, $ttl = null)
    {
        if ($this->error) {
            return false;
        }
        try {
            if ($namespace) {
                $key = $this->getStoreKey($namespace, $key);
            }
            if ($this->doSet($key, $value, $ttl) === true) {
                return true;
            }
            $this->critical();
            return false;
        } catch (Exception $e) {
            $this->critical($e);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function setMultiple($namespace, array $keys, $ttl = null)
    {
        if ($this->error) {
            return false;
        }
        try {
            if ($namespace) {
                $tmp = array();
                foreach ($keys as $key => &$value) {
                    $tmp[$this->getStoreKey($namespace, $key)] = $value;
                }
                if ($this->doSetMultiple($tmp, $ttl) === true) {
                    return true;
                }
                $this->critical();
                return false;
            }
            if ($this->doSetMultiple($keys, $ttl) === true) {
                return true;
            }
            $this->critical();
            return false;
        } catch (Exception $e) {
            $this->critical($e);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function delete($namespace, $key)
    {
        if ($this->error) {
            return false;
        }
        try {
            if ($namespace) {
                $key = $this->getStoreKey($namespace, $key);
            }
            if ($this->doDelete($key) === true) {
                return true;
            }
            $this->critical();
            return false;
        } catch (Exception $e) {
            $this->critical($e);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function deleteMultiple($namespace, array $keys)
    {
        if ($this->error) {
            return false;
        }
        try {
            if ($namespace) {
                $keys = $this->getStoreKeys($namespace, $keys);
            }
            if ($this->doDeleteMultiple($keys) === true) {
                return true;
            }
            $this->critical();
            return false;
        } catch (Exception $e) {
            $this->critical($e);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function exists($namespace, $key)
    {
        if ($this->error) {
            return false;
        }
        try {
            if ($namespace) {
                $key = $this->getStoreKey($namespace, $key);
            }
            $exists = $this->doExists($key);
            return $exists;
        } catch (Exception $e) {
            $this->critical($e);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    final public function clearNamespace($namespace)
    {
        if ($this->error) {
            return false;
        }
        try {
            $this->namespaces[$namespace] = $this->changeInvalidationKey($namespace);
            $this->log(LogLevel::INFO, "Invalidated cache items from the namespace $namespace");
            return true;
        } catch (Exception $e) {
            $this->critical($e);
            return false;
        }
    }

    /********************************************************************************
     * \WebScale\Driver\ErrorModeInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function setErrorMode($mode)
    {
        if (!in_array(
            $mode,
            array(
                DriverInterface::ERRMODE_EXCEPTION,
                DriverInterface::ERRMODE_WARNING,
                DriverInterface::ERRMODE_SILENT
            )
        )) {
            throw new InvalidArgumentException('Unknown error mode');
        }
        $this->errmode = $mode;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorMode()
    {
        return $this->errmode;
    }

    /********************************************************************************
     * \Psr\Log\LoggerAwareInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /********************************************************************************
     * Private
     *******************************************************************************/

    /**
     * Get invalidation key for the given namespace.
     *
     * @param string $namespacename
     * @return string
     */
    private function getInvalidationKey($namespace)
    {
        $versionid = $this->doGet('webscale-' . Version::getId() . ':' .  $namespace, $found);
        if ($found) {
            return $versionid;
        } else {
            return $this->changeInvalidationKey($namespace);
        }
    }

    /**
     * Change invalidation key for the given namespace.
     *
     * @param string $namespacename
     * @return string
     */
    private function changeInvalidationKey($namespace)
    {
        /**
         * Obvious solution for this would be an incrementing integer.
         * However, we use uniqid to prevent invalidated items from
         * coming back if this particular key gets evicted from the
         * data store.
         */
        $new = uniqid(mt_rand(), true);
        $ttl = WEBSCALE_TEST ? 10 : null;
        if ($this->doSet('webscale-' . Version::getId() . ':' .  $namespace, $new, $ttl)) {
            return $new;
        } else {
            throw new RuntimeException(
                "Cache backend did not save new invalidation key for the namespace $namespace"
            );
        }
    }

    /**
     * Get key used in the cache storage.
     *
     * @param string $namespacename
     * @param string $key
     * @return string
     */
    private function getStoreKey($namespace, $key)
    {
        $arr = explode(':', $namespace);
        $res_0 = '';
        $res_1 = '';
        foreach ($arr as $name) {
            $res_0 .= $name . ':';
            $res_2 = substr_replace($res_0, '', -1);
            if (!isset($this->namespaces[$res_2])) {
                $this->namespaces[$res_2] = $this->getInvalidationKey($res_2);
            }
            $res_1 .= $this->namespaces[$res_2];
        }
        return $namespace  . ':' . hash('md5', $res_1) . ':' . $key;
    }

    /**
     * Get keys used in the cache storage.
     *
     * @param string $namespacename
     * @param array $keys
     * @return array
     */
    private function getStoreKeys($namespace, array $keys)
    {
        $base = $this->getStoreKey($namespace, '');
        return array_map(function ($key) use ($base) {
            return $base . $key;
        }, $keys);
    }

    /********************************************************************************
     * Protected
     *******************************************************************************/

    protected function doGetMultiple(array $keys)
    {
        $out = array();
        foreach ($keys as $key) {
            $value = $this->doGet($key, $found);
            if ($found) {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    protected function doSetMultiple(array $keys, $ttl = null)
    {
        foreach ($keys as $key => $value) {
            if ($this->doSet($key, $value, $ttl)) {
                // ok
            } else {
                return false;
            }
        }
        return true;
    }

    protected function doDeleteMultiple(array $keys)
    {
        foreach ($keys as $key) {
            if ($this->doDelete($key)) {
                // ok
            } else {
                return false;
            }
        }
        return true;
    }

    protected function log($level, $message, array $context = array())
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    protected function critical(Exception $e = null)
    {
        $this->error = true;
        $message = 'Cache backend for the class ' . get_class($this) . ' does not work properly.';

        $this->log(LogLevel::CRITICAL, $message, array('e' => $e));

        if ($this->errmode === DriverInterface::ERRMODE_EXCEPTION) {
            if ($e) {
                throw $e;
            } else {
                throw new RuntimeException($message);
            }
        } elseif ($this->errmode === DriverInterface::ERRMODE_WARNING) {
            if ($e) {
                $message .= ' Message: ' . $e->getMessage();
            }
            trigger_error($message, E_USER_WARNING);
        }
    }

    /********************************************************************************
     * Abstract
     *******************************************************************************/

    abstract protected function doGet($key, &$found);

    abstract protected function doSet($key, $value, $ttl = null);

    abstract protected function doDelete($key);

    abstract protected function doExists($key);
}

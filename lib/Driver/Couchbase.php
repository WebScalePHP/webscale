<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Exception\RuntimeException;
use Couchbase as ExtCouchbase;

/**
 * Cache driver for Couchbase.
 */
class Couchbase extends AbstractMemcachedDriver
{
    protected $couchbase;

    /**
     * Constructor
     *
     * @param \Couchbase $couchbase
     */
    public function __construct(ExtCouchbase $couchbase)
    {
        $this->couchbase = $couchbase;
    }

    /**
     * Get the Couchbase instance used by the driver.
     *
     * @return \Couchbase
     * @codeCoverageIgnore
     */
    public function getCouchbase()
    {
        return $this->couchbase;
    }

    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        $value = $this->couchbase->get($key);
        if (false !== $value || $this->couchbase->getResultCode() === ExtCouchbase::SUCCESS) {
            $found = true;
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
        return $this->couchbase->getMulti($keys);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $value, $ttl = null)
    {
        $this->couchbase->set($key, $value, $this->formatTtl($ttl));
        if ($this->couchbase->getResultCode() === ExtCouchbase::SUCCESS) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSetMultiple(array $keys, $ttl = null)
    {
        $this->couchbase->setMulti($keys, $this->formatTtl($ttl));
        if ($this->couchbase->getResultCode() === ExtCouchbase::SUCCESS) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        $this->couchbase->delete($key);
        if (in_array($this->couchbase->getResultCode(), array(ExtCouchbase::SUCCESS, ExtCouchbase::KEY_ENOENT))) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        $this->couchbase->get($key);
        if ($this->couchbase->getResultCode() === ExtCouchbase::SUCCESS) {
            return true;
        }
        return false;
    }

    /********************************************************************************
     * \WebScale\Driver\DriverInterface
     *******************************************************************************/

    public static function isAvailable()
    {
        return extension_loaded('couchbase');
    }
}

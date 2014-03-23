<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Item;

use WebScale\Driver\DriverInterface;
use WebScale\Configuration;

/**
 * Multi-operation pipeline.
 */
class PipeContext
{
    /**
     * @ignore
     */
    protected $sets = array();

    /**
     * @ignore
     */
    protected $deletes = array();

    public function set($key, $value, $ttl)
    {
        if (isset($this->deletes[$key])) {
            unset($this->deletes[$key]);
        }
        $this->sets[$ttl][$key] = $value;
    }

    public function delete($key)
    {
        foreach ($this->sets as $ttlgroup => &$sets) {
            if (isset($sets[$key])) {
                unset($sets[$key]);
            }
        }
        $this->deletes[$key] = $key;
    }

    public function exec(
        $namespace,
        ItemCollectionInterface $collection,
        DriverInterface $driver,
        Configuration $config
    ) {
        foreach ($this->sets as $ttl => &$sets) {
            if ($config->distributeMisses()) {
                $ttl -= rand(0, floor($ttl / 10));
            }
            $driver->setMultiple($namespace, $sets, $ttl);
            foreach ($sets as $key => &$value) {
                $collection->getItem($key)->inject($value, true);
            }
        }
        if (count($this->deletes) > 0) {
            $driver->deleteMultiple($namespace, array_keys($this->deletes));
            foreach ($this->deletes as $key) {
                $collection->getItem($key)->inject(null, true);
            }
        }
    }
}

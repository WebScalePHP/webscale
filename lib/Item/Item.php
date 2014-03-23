<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Item;

use WebScale\Exception\InvalidArgumentException;
use WebScale\Exception\UnexpectedValueException;
use WebScale\Driver\DriverInterface;
use WebScale\Configuration;
use WebScale\Common\NullObject;
use DateTime;

class Item implements ItemInterface
{
    /**
     * @ignore
     */
    protected $namespace;

    /**
     * @ignore
     */
    protected $key;

    /**
     * @ignore
     */
    protected $driver;

    /**
     * @ignore
     */
    protected $config;

    /**
     * @ignore
     */
    protected $value;

    /**
     * @ignore
     */
    protected $isHit;

    /**
     * @ignore
     */
    protected $pipecontext;

    /**
     * Constructor
     *
     * @param string $namespace
     * @param string $key
     * @param \WebScale\Driver\DriverInterface $driver
     * @param \WebScale\Configuration $config
     */
    public function __construct($namespace, $key, DriverInterface $driver, Configuration $config)
    {
        $this->namespace = $namespace;
        $this->key = $key;
        $this->driver = $driver;
        $this->config = $config;
    }

    /********************************************************************************
     * \Psr\Cache\ItemInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $this->fetch();
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function isHit()
    {
        $this->fetch();
        return $this->isHit;
    }

    /**
     * {@inheritdoc}
     */
    public function set($value = null, $ttl = null)
    {
        switch (gettype($ttl)) {
            case 'integer':
                break;
            case 'object':
                if ($ttl instanceof DateTime) {
                    $ttl = $ttl->getTimestamp() - time();
                } else {
                    throw new InvalidArgumentException(
                        'Ttl must be eighter an integer, DateTime object or null'
                    );
                }
                break;
            case 'NULL':
                    $ttl = $this->config->getDefaultTtl();
                break;
            default:
                throw new InvalidArgumentException(
                    'Ttl must be an integer, DateTime object or null'
                );
                break;
        }
        if ($this->pipecontext) {
            $this->pipecontext->set($this->key, $value, $ttl);
            return true;
        }
        if ($ttl > 0 && $this->config->distributeMisses()) {
            $ttl -= rand(0, floor($ttl / 10));
        }
        if ($this->driver->set($this->namespace, $this->key, $value, $ttl) === true) {
            $this->value = $value;
            $this->isHit = true;
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        if ($this->pipecontext) {
            $this->pipecontext->delete($this->key);
            return $this;
        }
        $this->driver->delete($this->namespace, $this->key);
        $this->value = null;
        $this->isHit = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function exists()
    {
        return $this->driver->exists($this->namespace, $this->key);
    }

    /********************************************************************************
     * \WebScale\Item\ItemInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function inject($value, $isHit)
    {
        if ($isHit === true) {
            $this->value = $value;
            $this->isHit = $isHit;
        } elseif ($isHit === false) {
            $this->value = null;
            $this->isHit = $isHit;
        } else {
            throw new InvalidArgumentException(
                '$isHit bust be a boolean'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setPipeContext(PipeContext $context)
    {
        $this->pipecontext = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function unsetPipeContext()
    {
        unset($this->pipecontext);
        return $this;
    }

    /********************************************************************************
     * Protected
     *******************************************************************************/

    /**
     * Fetch value from the cache
     */
    protected function fetch()
    {
        if (isset($this->isHit)) {
            return;
        }
        $value = $this->driver->get($this->namespace, $this->key, $this->isHit);
        if ($this->isHit === true) {
            $this->value = $value;
        } elseif ($this->isHit === false) {
            $this->value = null;
        } else {
            throw new UnexpectedValueException(
                'Expected driver to set isHit to true or false. Got '. gettype($this->isHit)
            );
        }
    }
}

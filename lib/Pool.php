<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale;

use WebScale\Exception\InvalidArgumentException;
use WebScale\Driver\DriverInterface;
use WebScale\Item\ItemCollection;
use WebScale\Item\Item;

class Pool implements PoolInterface
{
    /**
     * @ignore
     */
    protected $namespace;

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
    protected $subpools = array();

    /**
     * Constructor
     *
     * @param \WebScale\Driver\DriverInterface $driver
     * @param string $namespace
     * @param \WebScale\Configuration|null $config
     */
    public function __construct(DriverInterface $driver, $namespace = 'webscale', Configuration $config = null)
    {
        if (!is_string($namespace)) {
            throw new InvalidArgumentException(
                'Namespace must be a string'
            );
        } elseif (strlen($namespace) === 0) {
            throw new InvalidArgumentException(
                'Namespace must have a length'
            );
        }
        $this->driver = $driver;
        $this->namespace = $namespace;
        $this->config = $config ? $config : new Configuration;
    }

    /********************************************************************************
     * \Psr\Cache\PoolInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                'Key must be a string'
            );
        }
        if (preg_match('/\{+|\}+|\(+|\)+|\/+|\\\\+|@+|:+/', $key)) {
            throw new InvalidArgumentException(
                'Characters {}()/\@: are reserved and cannot be used in keys'
            );
        }
        return new Item($this->namespace, $key, $this->driver, $this->config);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys)
    {
        $items = array();
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }
        return new ItemCollection($this->namespace, $items, $this->driver, $this->config);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $this->driver->clearNamespace($this->namespace);
        return $this;
    }

    /********************************************************************************
     * \WebScale\PoolInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function getSubPool($namespace)
    {
        if (!isset($this->subpools[$namespace])) {
            $class = get_class($this);
            $this->subpools[$namespace] = new $class(
                $this->driver,
                $this->namespace . ':' . $namespace,
                $this->config
            );
        }
        return $this->subpools[$namespace];
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getConfiguration()
    {
        return $this->config;
    }
}

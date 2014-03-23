<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Item;

use WebScale\Exception\InvalidArgumentException;
use WebScale\Exception\BadMethodCallException;
use WebScale\Exception\DomainException;
use WebScale\Driver\ExpirimentInterface;
use WebScale\Driver\DriverInterface;
use WebScale\Configuration;
use IteratorAggregate;
use ArrayIterator;
use DateTime;

class ItemCollection implements ItemCollectionInterface
{
    /**
     * @ignore
     */
    protected $namespace;

    /**
     * @ignore
     */
    protected $items;

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
    protected $fetched = false;

    /**
     * Constructor
     *
     * @param string $pool
     * @param array $keys
     * @param \WebScale\Driver\DriverInterface $driver
     * @param \WebScale\Configuration $config
     */
    public function __construct($namespace, array $items, DriverInterface $driver, Configuration $config)
    {
        $this->namespace = $namespace;
        $this->driver = $driver;
        $this->config = $config;
        $this->items = $items;
    }

    /********************************************************************************
     * \WebScale\Item\ItemCollectionInterface
     *******************************************************************************/

    public function pipe($callable /* + stuff you want to inject to $callable */)
    {
        if (count($args = func_get_args()) === 0) {
            throw new InvalidArgumentException('Pipe must have a callback');
        }
        if (!is_callable($callable = array_shift($args))) {
            throw new InvalidArgumentException('First argument must be a valid callback');
        }
        array_unshift($args, $this);

        $context = new PipeContext;

        foreach ($this->items as $item) {
            $item->setPipeContext($context);
        }

        call_user_func_array($callable, $args);

        $context->exec($this->namespace, $this, $this->driver, $this->config);

        return array_map(function ($item) {
            return $item->unsetPipeContext()->get();
        }, $this->items);
    }

    public function getItem($key)
    {
        $this->fetch();
        return $this->items[$item];
    }

    public function clear()
    {
        $this->fetched = true;
        $this->pipe(function ($collection) {
            foreach ($collection as $item) {
                $item->delete();
            }
        });
        return $this;
    }

    /********************************************************************************
     * \IteratorAggregate
     *******************************************************************************/

    public function getIterator()
    {
        $this->fetch();
        return new ArrayIterator($this->items);
    }

    /********************************************************************************
     * Protected
     *******************************************************************************/

    /**
     * Create items and fetch values from the cache
     */
    protected function fetch()
    {
        if ($this->fetched === true) {
            return;
        }
        $values = $this->driver->getMultiple($this->namespace, array_keys($this->items));
        foreach ($this->items as $key => $item) {
            if (array_key_exists($key, $values)) {
                $item->inject($values[$key], true);
            } else {
                $item->inject(null, false);
            }
        }
        $this->fetched = true;
    }
}

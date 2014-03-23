<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale;

use Psr\Cache\PoolInterface as Base;

interface PoolInterface /* extends Base */
{
    /**
     * Get the pool's subpool
     *
     * @param string $namespace
     * @return \WebScale\PoolInterface
     */
    public function getSubPool($namespace);

    /**
     * Get the pool's driver
     *
     * @return \WebScale\Driver\DriverInterface
     */
    public function getDriver();

    /**
     * Get the pool's configuration
     *
     * @return \WebScale\Configuration
     */
    public function getConfiguration();
}

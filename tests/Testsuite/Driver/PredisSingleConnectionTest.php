<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use WebScale\Driver\Factory;

class PredisSingleConnectionTest extends AbstractDriverTest
{
    protected function getDriver()
    {
        $driver = Factory::getRedisDriver(array(), true);
        return $driver;
    }
}

<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Tests\Driver;

use WebScale\Driver\Factory;

/**
 * @requires extension memcache
 */
class MemcacheTest extends AbstractDriverTest
{
    protected function getDriver()
    {
        return Factory::getMemcacheDriver();
    }
}

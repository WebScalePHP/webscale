<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Exception;

use Psr\Cache\InvalidArgumentException as BaseInterface;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface /*, BaseInterface */
{
}

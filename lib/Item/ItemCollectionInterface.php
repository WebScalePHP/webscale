<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Item;

use IteratorAggregate;

interface ItemCollectionInterface extends IteratorAggregate
{
    public function pipe($callable /* + stuff you want to inject to $callable */);

    public function getItem($item);

    public function clear();
}

<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Item;

use Psr\Cache\ItemInterface as Base;

interface ItemInterface /* extends Base */
{
    public function inject($value, $isHit);

    public function setPipeContext(PipeContext $context);

    public function unsetPipeContext();
}

<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver\Helper;

class ValueWrapper
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get()
    {
        return $this->data;
    }
}

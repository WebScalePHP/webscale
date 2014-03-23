<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale;

use WebScale\Exception\InvalidArgumentException;
use DateTime;

/**
 * Configuration class
 */
class Configuration
{
    /**
     * @ignore
     */
    protected $default_ttl = 1800; // 30 minutes

    /**
     * @ignore
     */
    protected $distribute_misses = false;

    /**
     * @ignore
     */
    public function getDefaultTtl()
    {
        return $this->default_ttl;
    }

    /**
     * Set default ttl for items
     *
     * @param int|\DateTime $ttl
     */
    public function setDefaultTtl($ttl)
    {
        switch (gettype($ttl)) {
            case 'integer':
                break;
            case 'object':
                if ($ttl instanceof DateTime) {
                    $ttl = $ttl->getTimestamp() - time();
                } else {
                    throw new InvalidArgumentException(
                        'Ttl must be eighter an integer or DateTime object'
                    );
                }
                break;
            default:
                throw new InvalidArgumentException(
                    'Ttl must be eighter an integer or DateTime object'
                );
                break;
        }
        $this->default_ttl = $ttl;
    }

    /**
     * Should misses be distributed?
     *
     * @return bool
     */
    public function distributeMisses()
    {
        return $this->distribute_misses;
    }

    /**
     * Should misses be distributed?
     *
     * Setting this to true will cause items' ttl to be reduced
     * by random amount (0-10%). This distibutes cache
     * misses between different requests.
     *
     * @param bool $bool
     */
    public function setDistributeMisses($bool)
    {
        if (is_bool($bool)) {
            $this->distribute_misses = $bool;
        } else {
            throw new InvalidArgumentException('$bool must be a boolean');
        }
    }
}

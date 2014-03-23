<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Session;

use WebScale\Exception\InvalidArgumentException;
use WebScale\Driver\DriverInterface;

/**
 * Session handler.
 */
class Handler extends AbstractSessionHandler
{
    /**
     * @ignore
     */
    protected $driver;

    /**
     * @ignore
     */
    protected $prefix;

    /**
     * Constructor
     *
     * @param \WebScale\Driver\DriverInterface $driver
     * @param string $prefix
     */
    public function __construct(DriverInterface $driver, $prefix = 'session:')
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException('Prefix must be a string');
        }
        $this->driver = $driver;
        $this->prefix = $prefix;
    }

    /********************************************************************************
     * \SessionHandlerInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function read($session_id)
    {
        return (string) $this->driver->get(null, $this->prefix . $session_id);
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data)
    {
        return $this->driver->set(null, $this->prefix . $session_id, $session_data, ini_get('session.gc_maxlifetime'));
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
        return $this->driver->delete(null, $this->prefix . $session_id);
    }
}

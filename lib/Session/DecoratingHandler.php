<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Session;

use WebScale\Exception\InvalidArgumentException;
use WebScale\Driver\DriverInterface;
use SessionHandlerInterface;

/**
 * Caches another session handler.
 */
class DecoratingHandler extends AbstractSessionHandler
{
    /**
     * @ignore
     */
    const SESSION_DATA = 0;

    /**
     * @ignore
     */
    const SESSION_WAS_CACHED = 1;

    /**
     * @ignore
     */
    protected $read_sessions = array();

    /**
     * @ignore
     */
    protected $cache;

    /**
     * @ignore
     */
    protected $wrapped;

    /**
     * @ignore
     */
    protected $cache_prefix;

    /**
     * @ignore
     */
    protected $cache_ttl;

    /**
     * Constructor
     *
     * @param \WebScale\Driver\DriverInterface $cache
     * @param \SessionHandlerInterface $wrapped
     * @param int $cache_ttl
     * @param string $cache_prefix
     */
    public function __construct(
        DriverInterface $cache,
        SessionHandlerInterface $wrapped,
        $cache_ttl = 300,
        $cache_prefix = 'session:'
    ) {
        if (!is_string($cache_prefix)) {
            throw new InvalidArgumentException('Prefix must be a string');
        }
        if (!is_integer($cache_ttl)) {
            throw new InvalidArgumentException('Ttl must be an integer');
        }
        $this->cache = $cache;
        $this->wrapped = $wrapped;
        $this->cache_ttl = $cache_ttl;
        $this->cache_prefix = $cache_prefix;
    }

    /********************************************************************************
     * \SessionHandlerInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function read($session_id)
    {
        if (isset($this->read_sessions[$session_id])) {
            return $this->read_sessions[$session_id][self::SESSION_DATA];
        }
        if (is_string($session_data = $this->cache->get(null, $this->cache_prefix . $session_id))) {
            $this->read_sessions[$session_id] = array(
                self::SESSION_DATA              => $session_data,
                self::SESSION_WAS_CACHED => true,
            );
            return $session_data;
        }
        $session_data = $this->wrapped->read($session_id);
        $this->read_sessions[$session_id] = array(
            self::SESSION_DATA              => $session_data,
            self::SESSION_WAS_CACHED => false,
        );
        $this->cache->set(null, $this->cache_prefix . $session_id, $session_data, $this->cache_ttl);
        return $session_data;
    }

    /**
     * {@inheritdoc}
     */
    public function write($session_id, $session_data)
    {
        if (isset(
            $this->read_sessions[$session_id])
            && $this->read_sessions[$session_id][self::SESSION_DATA] === $session_data
        ) {
            if ($this->read_sessions[$session_id][self::SESSION_WAS_CACHED]) {
                return true;
            }
            if ($this->wrapped->write($session_id, $session_data)) {
                return true;
            }
            return false;
        }
        if ($this->wrapped->write($session_id, $session_data)) {
            if ($this->cache->set(null, $this->cache_prefix . $session_id, $session_data, $this->cache_ttl)) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($session_id)
    {
        if (isset($this->read_sessions[$session_id])) {
            unset($this->read_sessions[$session_id]);
        }
        if ($this->wrapped->destroy($session_id)) {
            if ($this->cache->delete(null, $this->cache_prefix . $session_id)) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function open($save_path, $name)
    {
        return $this->wrapped->open($save_path, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return $this->wrapped->close();
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        return $this->wrapped->gc($maxlifetime);
    }
}

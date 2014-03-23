<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Session;

use SessionHandlerInterface;

/**
 * Abstract session handler.
 */
abstract class AbstractSessionHandler implements SessionHandlerInterface
{
    /********************************************************************************
     * \WebScale\Session\SessionHandlerInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
            return session_set_save_handler($this, true);
        } else {
            if (session_set_save_handler(
                array($this, 'open'),
                array($this, 'close'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destroy'),
                array($this, 'gc')
            )) {
                register_shutdown_function('session_write_close');
                return true;
            } else {
                return false;
            }
        }
    }

    /********************************************************************************
     * \SessionHandlerInterface
     *******************************************************************************/

    /**
     * Do nothing.
     */
    public function open($save_path, $name)
    {
        return true;
    }

    /**
     * Do nothing.
     */
    public function close()
    {
        return true;
    }

    /**
     * Do even more nothing.
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}

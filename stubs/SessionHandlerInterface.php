<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace
{
    /**
     * SessionHandlerInterface for PHP 5.3
     *
     * SessionHandlerInterface is an interface which defines a prototype for creating
     * a custom session handler. In order to pass a custom session handler to
     * session_set_save_handler() using its OOP invocation, the class must implement
     * this interface.
     *
     * @see http://www.php.net/manual/en/class.sessionhandlerinterface.php
     */
    interface SessionHandlerInterface
    {
        public function read($session_id);

        public function write($session_id, $session_data);

        public function destroy($session_id);

        public function open($save_path, $name);

        public function close();

        public function gc($maxlifetime);
    }
}

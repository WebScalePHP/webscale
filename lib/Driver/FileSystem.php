<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale\Driver;

use WebScale\Serializer\SerializerInterface;
use WebScale\Exception\InvalidArgumentException;
use Psr\Log\LogLevel;
use RegexIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Cache driver for file system.
 */
class FileSystem extends AbstractDriver
{
    /**
     * @ignore
     */
    const EXTENSION = '.cache';

    /**
     * @ignore
     */
    protected $directory;

    /**
     * @ignore
     */
    protected $serializer;

    /**
     * @ignore
     */
    protected $serializer_hash;

    /**
     * Constructor
     *
     * @param string $directory
     * @param array $options
     * @param bool $autoclean
     */
    public function __construct($directory, SerializerInterface $serializer = null, $autocleaning = true)
    {
        if (!is_dir($directory) && !@mkdir($directory, 0755, true)) {
            throw new InvalidArgumentException(
                sprintf('The directory "%s" does not exist and could not be created.', $directory)
            );
        }
        if (!is_writable($directory)) {
            throw new InvalidArgumentException(
                sprintf('The directory "%s" is not writable.', $directory)
            );
        }
        if (!file_exists($index = $directory . DIRECTORY_SEPARATOR . 'index.php')) {
            touch($index);
        }

        // Everyone uses Apache :-) SRSLY, do not use web-accessible directory.
        if (!file_exists($htaccess = $directory . DIRECTORY_SEPARATOR . '.htaccess')) {
            $extension = $this::EXTENSION;
            $contents = <<<HTACCESS
<FilesMatch ".($extension)$">
    Order allow,deny
    Deny from all
</FilesMatch>
HTACCESS;
            file_put_contents($htaccess, $contents);
            chmod($htaccess, 0644);
        }

        $this->directory = realpath($directory);
        $this->serializer = $serializer ? $serializer : Factory::getSerializer(true, false);
        $this->serializer_class = get_class($this->serializer);

        // @codeCoverageIgnoreStart
        if ($autocleaning && rand(0, 99) == 0) {
            register_shutdown_function(array($this, 'clean'));
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Clean unused items
     *
     * @param int $interval
     *     Do not perform cleaning if last clean operation was performed
     *     less than $interval seconds ago
     * @param int $min_unused
     *     Items that haven't been used in more than $min_unused
     *     seconds will be removed.
     * @return bool
     */
    public function clean($interval = 3600, $min_unused = 604800)
    {
        if (file_exists($cleanfile = $this->directory . DIRECTORY_SEPARATOR . 'lastclean')) {
            if (filemtime($cleanfile) >= time() - $interval) {
                return false;
            }
        }
        touch($cleanfile);
        $iterator = $this->getIterator();
        $iterator->rewind();
        while ($iterator->valid()) {
            $file = $iterator->key();
            if (fileatime($file) <= time() - $min_unused) {
                @unlink($file);
            }
            $iterator->next();
        }
        return true;
    }

    /********************************************************************************
     * \WebScale\Driver\AbstractDriver
     *******************************************************************************/

    /**
     * {@inheritdoc}
     */
    protected function doGet($key, &$found)
    {
        if (false === ($resource = @fopen($this->hashKey($key), 'r'))) {
            $found = false;
            return false;
        }

        $expires = (int) fgets($resource);

        // covered by doExists
        // @codeCoverageIgnoreStart
        if ($expires !== 0 && $expires < time()) {
            fclose($resource);
            $found = false;
            return null;
        }
        // @codeCoverageIgnoreEnd

        $data = '';
        while (false !== ($line = fgets($resource))) {
            $data .= $line;
        }
        fclose($resource);

        $found = true;
        return $this->serializer->unserialize($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function doSet($key, $data, $ttl = null)
    {
        if (!is_dir($filepath = pathinfo($filename = $this->hashKey($key), PATHINFO_DIRNAME))) {
            if (true !== @mkdir($filepath, 0755, true)) {
            }
            touch($filepath . DIRECTORY_SEPARATOR . 'index.php');
            if (!file_exists($index = dirname($filepath) . DIRECTORY_SEPARATOR . 'index.php')) {
                touch($index);
            }
        }

        $expires = is_null($ttl) ? 0 : time() + $ttl;

        if (false !== file_put_contents(
            $tmpname = tempnam($filepath, basename($filename)),
            $expires . PHP_EOL . $this->serializer->serialize($data)
        )) {
            if (rename($tmpname, $filename)) {
                return true;
            }
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDelete($key)
    {
        @unlink($this->hashKey($key));
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExists($key)
    {
        if (false === ($resource = @fopen($this->hashKey($key), 'r'))) {
            return false;
        }
        $expires = (int) fgets($resource);
        fclose($resource);
        if ($expires !== 0 && $expires < time()) {
            return false;
        }
        return true;
    }

    /********************************************************************************
     * \WebScale\Driver\DriverInterface
     *******************************************************************************/

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public static function isAvailable()
    {
        return true;
    }

    /********************************************************************************
     * Protected
     *******************************************************************************/

    protected function hashKey($key)
    {
        $key = hash('sha1', $key . $this->serializer_class);
        $path = substr($key, 0, 1) . DIRECTORY_SEPARATOR . substr($key, 1, 2) . DIRECTORY_SEPARATOR . substr($key, 3);
        return $this->directory . DIRECTORY_SEPARATOR . $path . $this::EXTENSION;
    }

    protected function getIterator()
    {
        $iterator = new RecursiveDirectoryIterator($this->directory);
        $iterator = new RecursiveIteratorIterator($iterator);
        return new RegexIterator($iterator, '/^.+\\' . $this::EXTENSION . '$/i');
    }
}

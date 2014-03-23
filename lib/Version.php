<?php
/**
 * This file is part of the WebScale library. It is licenced under
 * MIT licence. For more information, see LICENSE file that
 * was distributed with this library.
 */
namespace WebScale;

class Version
{
    private static $warnings = array();

    public static function get()
    {
        $version            = static::getVersion();

        $major              = $version[0];
        $medium           = $version[1];
        $minor              = $version[2];
        $special             = $version[3];
        $specialNumber  = $version[4];

        $result = $major . '.' . $medium . '.' . $minor . " ";
        switch ($special) {
            case 0:
                $suffix = "-DEV " . $specialNumber;
                break;
            case 1:
                $suffix = "ALPHA " . $specialNumber;
                break;
            case 2:
                $suffix = "BETA " . $specialNumber;
                break;
            case 3:
                $suffix = "RC " . $specialNumber;
                break;
            default:
                $suffix = "";
                break;
        }
        $result .= $suffix;
        return trim($result);
    }

    public static function getId()
    {
        $version            = static::getVersion();

        $major              = $version[0];
        $medium           = $version[1];
        $minor              = $version[2];
        $special             = $version[3];
        $specialNumber  = $version[4];

        return $major . sprintf("%02s", $medium) . sprintf("%02s", $minor) . $special . $specialNumber;
    }

    public static function warn($message, $error_type = E_USER_DEPRECATED)
    {
        $class = get_called_class();
        if (!isset(self::$warnings[$class]) || self::$warnings[$class]) {
            trigger_error($message, $error_type);
        }
    }

    public static function disableWarnings()
    {
        $class = get_called_class();
        self::$warnings[$class] = false;
    }

    public static function compare($version, $callback = null)
    {
        $currentVersion = explode(' ', self::get());
        $res = version_compare($version, $currentVersion[0]);
        if (is_callable($callback)) {
            call_user_func($callback, $res);
        }
        return $res;
    }

    protected static function getVersion()
    {
        return array(
            0, // Major version
            7, // Med version
            0, // Min version
            2, // Special release: 1 = Alpha, 2 = Beta, 3 = RC, 4 = Stable
            1  // Special release version i.e. RC1, Beta2 etc.
        );
    }
}

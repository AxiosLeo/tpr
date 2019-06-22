<?php

namespace tpr;

/**
 * Class Path.
 *
 * @method string root($path = null)      static
 * @method string app($path = null)       static
 * @method string config($path = null)    static
 * @method string runtime($path = null)   static
 * @method string vendor($path = null)    static
 * @method string framework($path = null) static
 * @method string index($path = null)     static
 * @method string views($path = null)     static
 */
class Path
{
    private static $path = [];

    private static function set($path_name, $path)
    {
        self::$path[$path_name] = $path;
        return self::$path[$path_name];
    }

    private static function get($path_name)
    {
        return isset(self::$path[$path_name]) ? self::$path[$path_name] : '';
    }

    public static function check()
    {
        if (empty(self::framework())) {
            self::framework(dirname(__DIR__) . DIRECTORY_SEPARATOR);
        }
        if (empty(self::root())) {
            self::root(dirname(dirname(dirname(self::framework()))) . DIRECTORY_SEPARATOR);
        }
        if (empty(self::app())) {
            self::app(self::root() . 'application' . DIRECTORY_SEPARATOR);
        }
        if (empty(self::runtime())) {
            self::runtime(self::root() . 'runtime' . DIRECTORY_SEPARATOR);
        }
        if (empty(self::vendor())) {
            self::vendor(self::root() . 'vendor' . DIRECTORY_SEPARATOR);
        }
        if (empty(self::index())) {
            self::index(self::root() . "public" . DIRECTORY_SEPARATOR);
        }
        if (empty(self::config())) {
            self::config(self::root() . "config" . DIRECTORY_SEPARATOR);
        }
        if (empty(self::views())) {
            self::views(self::root() . "views" . DIRECTORY_SEPARATOR);
        }
        return self::all();
    }

    public static function cache()
    {
        return self::runtime() . App::appName() . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR;
    }

    public static function all()
    {
        return [
            "framework" => self::framework(),
            "root"      => self::root(),
            "app"       => self::app(),
            "config"    => self::config(),
            "runtime"   => self::runtime(),
            "vendor"    => self::vendor(),
            "index"     => self::index(),
            "views"     => self::views()
        ];
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed|string
     */
    public static function __callStatic($name, $arguments)
    {
        if (empty($arguments)) {
            return self::get($name);
        } elseif (empty($arguments[0])) {
            return self::get($name);
        } else {
            return self::set($name, $arguments[0]);
        }
    }

    public static function format($path, $create = false)
    {
        $path = substr($path, -1) != DIRECTORY_SEPARATOR ? $path . DIRECTORY_SEPARATOR : $path;
        if ($create && !file_exists($path)) {
            if (!mkdir($path, 0700, true)) {
                return null;
            }
        }
        return $path;
    }

    public static function dir($arrayDirItem, $divider = DIRECTORY_SEPARATOR)
    {
        $path = "";
        foreach ($arrayDirItem as $item) {
            $path .= $item . $divider;
        }
        return $path;
    }
}

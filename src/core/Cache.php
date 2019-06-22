<?php

namespace tpr\core;

use tpr\core\cache\Driver;

class Cache
{
    /**
     * @var Driver
     */
    private $driver;

    public function __construct()
    {
        $driver       = \tpr\Config::get("cache.driver", "file");
        $this->driver = $this->driver($driver);
        if (is_null($this->driver)) {
            $this->driver = $this->driver("file");
        }
    }

    public function driver($driver_name)
    {
        if (class_exists($driver_name)) {
            return new $driver_name;
        }
        $driver = "\\tpr\\core\\cache\\" . ucfirst($driver_name);
        return class_exists($driver) ? new $driver : null;
    }

    public function set($key, $data, $timeout = 0)
    {
        $this->driver->set($key, $data, $timeout);
    }

    public function get($key)
    {
        return $this->driver->get($key);
    }

    public function has($key)
    {
        return $this->driver->has($key);
    }

    public function rm($key)
    {
        return $this->driver->rm($key);
    }

    public function inc($key, $step = 1)
    {
        return $this->driver->inc($key, $step);
    }

    public function dec($key, $step = 1)
    {
        return $this->driver->dec($key, $step);
    }

    public function clear($tag = null)
    {
        return $this->driver->clear($tag);
    }

    public function pull($key)
    {
        return $this->driver->pull($key);
    }

    public function remember($key, $value, $expire = null)
    {
        return $this->driver->remember($key, $value, $expire);
    }

    public function tag($key, $keys = null, $overlay = false)
    {
        return $this->driver->tag($key, $keys, $overlay);
    }
}
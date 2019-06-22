<?php

namespace tpr\lib;

use tpr\exception\ContainerNotExistException;

abstract class Facade
{
    protected $name;

    /**
     * @return mixed
     */
    abstract protected static function getContainName();

    /**
     * @return string
     */
    protected static function getFacadeClass()
    {
        return null;
    }

    public function __call($func, $arguments)
    {
        return self::dispatch($func, $arguments);
    }

    public static function __callStatic($func, $arguments)
    {
        return self::dispatch($func, $arguments);
    }

    /**
     * @param string $func
     * @param array  $arguments
     *
     * @return mixed
     */
    private static function dispatch($func, $arguments)
    {
        $name = static::getContainName();
        if (!Container::has($name)) {
            if (!is_null(static::getFacadeClass())) {
                Container::set($name, static::getFacadeClass());
            } else {
                throw new ContainerNotExistException("`" . $name . "` Container is not exist");
            }
        }
        return call_user_func_array([Container::get($name), $func], $arguments);
    }
}
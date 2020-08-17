<?php

declare(strict_types=1);

namespace tpr;

use tpr\exception\ContainerNotExistException;

abstract class Facade
{
    public function __call($func, $arguments)
    {
        return self::dispatch($func, $arguments);
    }

    public static function __callStatic($func, $arguments)
    {
        return self::dispatch($func, $arguments);
    }

    /**
     * @return string
     */
    abstract protected static function getContainName();

    abstract protected static function getFacadeClass();

    private static function dispatch($func, $arguments)
    {
        $name = static::getContainName();
        if (!Container::has($name)) {
            if (null !== static::getFacadeClass()) {
                Container::bind($name, static::getFacadeClass());
            } else {
                throw new ContainerNotExistException($name);
            }
        }

        return \call_user_func_array([Container::get($name), $func], $arguments);
    }
}

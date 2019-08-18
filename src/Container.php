<?php

declare(strict_types=1);

namespace tpr;

use ArrayAccess;
use InvalidArgumentException;
use tpr\server\ServerAbstract;
use tpr\core\Config;
use tpr\core\Lang as CoreLang;
use tpr\core\request\RequestAbstract;
use tpr\core\Response;
use tpr\core\Template;
use tpr\exception\ClassNotExistException;
use tpr\exception\ContainerNotExistException;

/**
 * Class Container.
 *
 * @method Config          config()   static
 * @method RequestAbstract request()  static
 * @method Response        response() static
 * @method Template        template() static
 * @method ServerAbstract  app()      static
 * @method CoreLang        lang()     static
 */
final class Container implements ArrayAccess
{
    private static $object = [];

    public static function __callStatic($name, $arguments)
    {
        if (!isset(self::$object[$name])) {
            throw new ContainerNotExistException($name);
        }

        return self::$object[$name];
    }

    /**
     * @param string        $name
     * @param object|string $class
     * @param array         $params
     */
    public static function bind(string $name, $class, array $params = []): void
    {
        if (\is_string($class)) {
            if (!class_exists($class)) {
                throw new ClassNotExistException($name);
            }
            $class = new $class($params);
        }
        if (!\is_object($class)) {
            throw new InvalidArgumentException('$class is invalid argument : ' . \gettype($class));
        }
        self::$object[$name] = $class;
    }

    /**
     * Bind when not exist.
     *
     * @param string $name
     * @param        $class
     * @param array  $params
     */
    public static function bindNX(string $name, $class, array $params = [])
    {
        if (!self::has($name)) {
            self::bind($name, $class, $params);
        }
    }

    public static function import(array $classArray): void
    {
        foreach ($classArray as $key => $class) {
            self::bindNX($key, $class);
        }
    }

    public static function get(string $name)
    {
        if (isset(self::$object[$name])) {
            return self::$object[$name];
        }

        return null;
    }

    public static function has(string $name): bool
    {
        return isset(self::$object[$name]);
    }

    public static function delete(string $name): void
    {
        if (isset(self::$object[$name])) {
            unset(self::$object[$name]);
        }
    }

    public function offsetExists($key): bool
    {
        return self::has($key);
    }

    public function offsetGet($key)
    {
        return self::get($key);
    }

    public function offsetSet($key, $value): void
    {
        self::bind($key, $value);
    }

    public function offsetUnset($key): void
    {
        self::delete($key);
    }
}

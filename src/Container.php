<?php

declare(strict_types=1);

namespace tpr;

use ArrayAccess;
use Rakit\Validation\Validator;
use tpr\core\Config;
use tpr\core\Dispatch;
use tpr\core\Lang as CoreLang;
use tpr\core\request\RequestAbstract;
use tpr\core\Response;
use tpr\core\Template;
use tpr\exception\ClassNotExistException;
use tpr\exception\ContainerNotExistException;
use tpr\server\ServerHandler;

/**
 * Class Container.
 *
 * @method Config          config()    static
 * @method RequestAbstract request()   static
 * @method Response        response()  static
 * @method Template        template()  static
 * @method ServerHandler   app()       static
 * @method CoreLang        lang()      static
 * @method Validator       validator() static
 */
final class Container implements ArrayAccess
{
    private static array $object = [];

    public static function __callStatic($name, $arguments)
    {
        if (!isset(self::$object[$name])) {
            throw new ContainerNotExistException($name);
        }

        return self::$object[$name];
    }

    /**
     * @return Dispatch|object
     */
    public static function dispatch(): ?object
    {
        return self::get('cgi_dispatch');
    }

    public static function bind(string $name, string $class, ...$params): void
    {
        if (!class_exists($class)) {
            throw new ClassNotExistException($name);
        }
        $class               = new $class(...$params);
        self::$object[$name] = $class;
    }

    public static function bindWithObj(string $name, object $class): void
    {
        self::$object[$name] = $class;
    }

    public static function bindNX(string $name, string $class, array $params = []): void
    {
        if (!self::has($name)) {
            // Bind when not exist.
            self::bind($name, $class, $params);
        }
    }

    public static function bindNXWithObj(string $name, object $class): void
    {
        if (!self::has($name)) {
            // Bind when not exist.
            self::bindWithObj($name, $class);
        }
    }

    public static function import(array $classArray): void
    {
        foreach ($classArray as $key => $class) {
            self::bindNX($key, $class);
        }
    }

    public static function get(string $name): ?object
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

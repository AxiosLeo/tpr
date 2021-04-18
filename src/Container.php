<?php

declare(strict_types=1);

namespace tpr;

use ArrayAccess;
use Rakit\Validation\Validator;
use tpr\core\Config;
use tpr\core\Dispatch;
use tpr\core\Lang as CoreLang;
use tpr\core\request\RequestInterface;
use tpr\core\Response;
use tpr\exception\ClassNotExistException;
use tpr\exception\ContainerNotExistException;
use tpr\server\ServerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Class Container.
 *
 * @method Config           config()    static
 * @method RequestInterface request()   static
 * @method Response         response()  static
 * @method ServerInterface  app()       static
 * @method CoreLang         lang()      static
 * @method Validator        validator() static
 * @method \tpr\core\Event  event()     static
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

    public static function template(): Environment
    {
        if (!self::has('template')) {
            $twig = new Environment(new FilesystemLoader(Path::views()), []);
            if (!App::debugMode()) {
                $twig->setCache(path_join(Path::cache(), 'views'));
            }
            self::bindWithObj('template', $twig);
        } else {
            $twig = self::get('template');
        }

        return $twig;
    }

    public static function bind(string $name, string $class_name, ...$params): void
    {
        if (!class_exists($class_name)) {
            throw new ClassNotExistException($name);
        }
        $object = new $class_name(...$params);
        self::bindWithObj($name, $object);
    }

    public static function bindWithObj(string $name, object $object): void
    {
        self::$object[$name] = $object;
    }

    public static function bindNX(string $name, string $class_name, array $params = []): void
    {
        if (!self::has($name)) {
            // Bind when not exist.
            self::bind($name, $class_name, $params);
        }
    }

    public static function bindNXWithObj(string $name, object $object): void
    {
        if (!self::has($name)) {
            // Bind when not exist.
            self::bindWithObj($name, $object);
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

    public function offsetExists($offset): bool
    {
        return self::has($offset);
    }

    public function offsetGet($offset)
    {
        return self::get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        if (\is_string($value)) {
            self::bind($offset, $value);
        } else {
            self::bindWithObj($offset, $value);
        }
    }

    public function offsetUnset($offset): void
    {
        self::delete($offset);
    }
}

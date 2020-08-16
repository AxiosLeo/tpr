<?php

declare(strict_types=1);

namespace tpr;

use Closure;
use tpr\exception\ClassNotExistException;

/**
 * Class Event.
 *
 * @see CoreEvent
 */
class Event
{
    private static array $events = [];

    /**
     * 批量添加事件，不支持自定义方法和操作置顶.
     */
    public static function import(array $events): void
    {
        foreach ($events as $event_name => $event) {
            if (\is_string($event)) {
                self::add($event_name, $event);
            } elseif (\is_array($event)) {
                foreach ($event as $event_item) {
                    self::add($event_name, $event_item);
                }
            }
        }
    }

    /**
     * 添加事件.
     *
     * @param string                $class
     * @param Closure|object|string $method
     */
    public static function add(string $name, $class, string $method = 'run', array $params = [], bool $first = false): void
    {
        if (!isset(self::$events[$name])) {
            self::$events[$name] = [];
        }

        if (\is_string($class) && !class_exists($class)) {
            throw new ClassNotExistException($class);
        }

        $event = [
            'class'  => $class,
            'method' => $method,
            'params' => $params,
        ];

        $first ? array_unshift(self::$events[$name], $event) : array_push(self::$events[$name], $event);
    }

    /**
     * 事件触发器.
     *
     * @param array $data
     */
    public static function trigger(string $name, ...$data): void
    {
        self::listen($name, $data);
    }

    /**
     * 监听事件.
     *
     * @param mixed $data
     */
    public static function listen(string $name, &$data = [], Closure $callback = null): void
    {
        if (isset(self::$events[$name])) {
            foreach (self::$events[$name] as $event) {
                self::exec($event, $data, $callback);
            }
        }
    }

    /**
     * 仅监听某个事件组中的第一个.
     *
     * @param mixed $data
     */
    public static function listenFirst(string $name, &$data = [], Closure $callback = null): void
    {
        if (isset(self::$events[$name], self::$events[$name][0])) {
            self::exec(self::$events[$name][0], $data, $callback);
        }
    }

    /**
     * 获取事件数组.
     */
    public static function get(string $name = null): array
    {
        if (null === $name) {
            return self::$events;
        }

        return isset(self::$events[$name]) ? self::$events[$name] : [];
    }

    /**
     * 移除事件中的某个操作.
     */
    public static function remove(string $name, int $index): bool
    {
        if (isset(self::$events[$name][$index])) {
            unset(self::$events[$name][$index]);
            self::$events[$name] = array_values(self::$events[$name]);

            return true;
        }

        return false;
    }

    /**
     * 删除事件.
     */
    public static function delete(string $name): bool
    {
        if (isset(self::$events[$name])) {
            unset(self::$events[$name]);

            return true;
        }

        return false;
    }

    /**
     * 执行某个事件.
     *
     * @param array $event
     * @param mixed $data
     */
    private static function exec($event, &$data, Closure $callback = null): void
    {
        $class       = $event['class'];
        $method      = $event['method'];
        $extra_param = $event['params'];
        if ($class instanceof Closure) {
            $result = \call_user_func_array($class, [&$data]);
        } elseif (\is_object($class)) {
            $result = \call_user_func_array([$class, $method], [&$data, $extra_param]);
        } else {
            $obj    = new $class();
            $result = \call_user_func_array([$obj, $method], [&$data, $extra_param]);
        }
        if (null !== $callback) {
            \call_user_func_array($callback, [&$data, $result]);
        }
    }
}

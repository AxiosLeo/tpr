<?php

namespace tpr;

use tpr\core\Event as CoreEvent;

/**
 * Class Event.
 *
 * @see CoreEvent
 */
class Event
{
    private static $events = [];

    /**
     * 批量添加行为，不支持自定义方法和操作置顶.
     *
     * @param array $events
     */
    public static function import(array $events) : void
    {
        foreach ($events as $event_name => $event) {
            if (is_string($event)) {
                self::add($event_name, $event);
            } elseif (is_array($event)) {
                foreach ($event as $event_item) {
                    self::add($event_name, $event_item);
                }
            }
        }
    }

    /**
     * 添加行为.
     *
     * @param string                 $name
     * @param string                 $class
     * @param string|object|\Closure $method
     * @param array                  $params
     * @param bool                   $first
     */
    public static function add(string $name, $class, string $method = 'run', $params = [], $first = false) : void
    {
        if (!isset(self::$events[$name])) {
            self::$events[$name] = [];
        }

        if (is_string($class) && !class_exists($class)) {
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
     * 监听行为.
     *
     * @param string        $name
     * @param mixed         $data
     * @param \Closure|null $callback
     */
    public static function listen(string $name, &$data = [], \Closure $callback = null) : void
    {
        if (isset(self::$events[$name])) {
            foreach (self::$events[$name] as $event) {
                self::exec($event, $data, $callback);
            }
        }
    }

    /**
     * 仅监听某个行为数组中的第一个.
     *
     * @param string        $name
     * @param mixed         $data
     * @param \Closure|null $callback
     */
    public static function listenFirst(string $name, &$data = [], \Closure $callback = null) : void
    {
        if (isset(self::$events[$name], self::$events[$name][0])) {
            self::exec(self::$events[$name][0], $data, $callback);
        }
    }

    /**
     * 获取行为数组.
     *
     * @param string|null $name
     *
     * @return array
     */
    public static function get(string $name = null) : array
    {
        if (is_null($name)) {
            return self::$events;
        }

        return isset(self::$events[$name]) ? self::$events[$name] : [];
    }

    /**
     * 移除行为中的某个操作.
     *
     * @param string $name
     * @param int    $index
     *
     * @return bool
     */
    public static function remove(string $name, int $index) : bool
    {
        if (isset(self::$events[$name][$index])) {
            unset(self::$events[$name][$index]);
            self::$events[$name] = array_values(self::$events[$name]);

            return true;
        }

        return false;
    }

    /**
     * 删除行为.
     *
     * @param string $name
     *
     * @return bool
     */
    public static function delete(string $name) : bool
    {
        if (isset(self::$events[$name])) {
            unset(self::$events[$name]);

            return true;
        }

        return false;
    }

    /**
     * 执行某个行为.
     *
     * @param               $event
     * @param mixed         $data
     * @param \Closure|null $callback
     */
    private static function exec($event, &$data, \Closure $callback = null) : void
    {
        $class       = $event['class'];
        $method      = $event['method'];
        $extra_param = $event['params'];
        if ($class instanceof \Closure) {
            $result = call_user_func_array($class, [&$data]);
        } elseif (is_object($class)) {
            $result = call_user_func_array([$class, $method], [&$data, $extra_param]);
        } else {
            $obj    = new $class();
            $result = call_user_func_array([$obj, $method], [&$data, $extra_param]);
        }
        if (!is_null($callback)) {
            call_user_func_array($callback, [&$data, $result]);
        }
    }
}

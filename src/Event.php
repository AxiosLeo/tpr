<?php

declare(strict_types=1);

namespace tpr;

/**
 * Class Event.
 */
class Event
{
    private static array $events = [];

    private static ?Event $instance = null;

    private function __construct()
    {
        $events = Config::get('events', []);
        foreach ($events as $event) {
            list($event_name, $class, $method) = explode('::', $event['handler'], 3);
            self::register($event_name, $class, $method);
        }
    }

    /**
     * register event by class method.
     */
    public static function register(string $event_name, string $class, string $method): void
    {
        if (!class_exists($class) && method_exists($class, $method)) {
            throw new \RuntimeException('Class or Method Not Exist : ' . $class . ':' . $method, 404);
        }
        $obj = new $class();
        self::registerWithObj($event_name, $obj, $method);
    }

    /**
     * register event by object.
     */
    public static function registerWithObj(string $event_name, object $class, string $method): void
    {
        $closure = function (&$data) use ($class, $method) {
            return \call_user_func_array([$class, $method], [&$data]);
        };
        self::on($event_name, $closure);
    }

    /**
     * register event by \Closure.
     */
    public static function on(string $event_name, \Closure $closure)
    {
        self::init();
        if (!isset(self::$events[$event_name])) {
            self::$events[$event_name] = [];
        }
        array_push(self::$events[$event_name], $closure);
    }

    /**
     * listen event, support callback.
     *
     * @param null|mixed $data
     */
    public static function listen(string $event_name, &$data = null, ?\Closure $callback = null): void
    {
        if (!isset(self::$events[$event_name])) {
            return;
        }
        foreach (self::$events[$event_name] as $event) {
            $result = \call_user_func_array($event, [&$data]);
            if (null !== $callback) {
                \call_user_func_array($callback, [&$data, $result]);
            }
        }
    }

    /**
     * trigger event.
     */
    public static function trigger(string $event_name, ...$params): void
    {
        if (!isset(self::$events[$event_name])) {
            return;
        }
        foreach (self::$events[$event_name] as $event) {
            \call_user_func_array($event, $params);
        }
    }

    /**
     * the number of event.
     */
    public static function size(string $event_name): int
    {
        if (!isset(self::$events[$event_name])) {
            return 0;
        }

        return \count(self::$events[$event_name]);
    }

    /**
     * delete events by name of event.
     */
    public static function delete(string $event_name): void
    {
        unset(self::$events[$event_name]);
    }

    /**
     * remove event by index.
     *
     * @return bool
     */
    public static function remove(string $event_name, int $index = 0)
    {
        if (isset(self::$events[$event_name][$index])) {
            unset(self::$events[$event_name][$index]);
            self::$events[$event_name] = array_values(self::$events[$event_name]);

            return true;
        }

        return false;
    }

    /**
     * initialize only when registering some events.
     *
     * @return null|Event
     */
    private static function init()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

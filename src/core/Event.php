<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\Config;

class Event
{
    private array $events = [];

    public function __construct()
    {
        $events = Config::get('events', []);
        foreach ($events as $event) {
            list($event_name, $class, $method) = explode('::', $event['handler'], 3);
            self::register($event_name, $class, $method);
        }
    }

    /**
     * register event by class method.
     *
     * @param $event_name
     * @param $class
     * @param $method
     */
    public function register($event_name, $class, $method)
    {
        if (!class_exists($class) && method_exists($class, $method)) {
            throw new \RuntimeException('Class or Method Not Exist : ' . $class . ':' . $method, 404);
        }
        $obj = new $class();
        $this->registerWithObj($event_name, $obj, $method);
    }

    /**
     * register event by object.
     */
    public function registerWithObj(string $event_name, object $object, string $method)
    {
        $closure = function (...$params) use ($object, $method) {
            return \call_user_func_array([$object, $method], $params);
        };
        $this->on($event_name, $closure);
    }

    /**
     * register event by \Closure.
     */
    public function on(string $event_name, \Closure $closure)
    {
        if (!isset($this->events[$event_name])) {
            $this->events[$event_name] = [];
        }
        array_push($this->events[$event_name], $closure);
    }

    /**
     * listen event, support callback.
     *
     * @param null|mixed $data
     */
    public function listen(string $event_name, &$data = null, ?\Closure $callback = null): void
    {
        if (!isset($this->events[$event_name])) {
            return;
        }
        foreach ($this->events[$event_name] as $event) {
            $data = \call_user_func_array($event, [$data]);
            if (null !== $callback) {
                \call_user_func_array($callback, [$data]);
            }
        }
    }

    /**
     * trigger event.
     */
    public function trigger(string $event_name, ...$params): void
    {
        if (!isset($this->events[$event_name])) {
            return;
        }
        foreach ($this->events[$event_name] as $event) {
            \call_user_func_array($event, $params);
        }
    }

    /**
     * the number of event.
     */
    public function size(string $event_name): int
    {
        if (!isset($this->events[$event_name])) {
            return 0;
        }

        return \count($this->events[$event_name]);
    }

    /**
     * delete events by name of event.
     */
    public function delete(string $event_name): void
    {
        unset($this->events[$event_name]);
    }

    /**
     * remove event by index.
     */
    public function remove(string $event_name, int $index = 0): bool
    {
        if (isset($this->events[$event_name][$index])) {
            unset($this->events[$event_name][$index]);
            $this->events[$event_name] = array_values($this->events[$event_name]);

            return true;
        }

        return false;
    }

    /**
     * get events.
     */
    public function get(string $event_name): array
    {
        return isset($this->events[$event_name]) ? $this->events[$event_name] : [];
    }
}

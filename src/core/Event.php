<?php

declare(strict_types=1);

namespace tpr\core;

class Event
{
    private array $events = [];

    public function __construct()
    {
        // import events from config
        $events = \tpr\Config::get('events', []);
        foreach ($events as $event) {
            list($event_name, $class, $method) = explode('::', $event['handler'], 3);
            $this->register($event_name, $class, $method);
        }
    }

    /**
     * register event by class method.
     */
    public function register(string $event_name, string $class, string $method): void
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
    public function registerWithObj(string $event_name, object $class, string $method): void
    {
        $closure = function (&$data) use ($class, $method) {
            return \call_user_func_array([$class, $method], [&$data]);
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
        array_push($this->events, $closure);
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
            $result = \call_user_func_array($event, [&$data]);
            if (null !== $callback) {
                \call_user_func_array($callback, [&$data, $result]);
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
     *
     * @return bool
     */
    public function remove(string $event_name, int $index = 0)
    {
        if (isset($this->events[$event_name][$index])) {
            unset($this->events[$event_name][$index]);
            $this->events[$event_name] = array_values($this->events[$event_name]);

            return true;
        }

        return false;
    }
}

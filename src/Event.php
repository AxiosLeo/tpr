<?php

declare(strict_types=1);

namespace tpr;

/**
 * Class Event.
 *
 * @see CoreEvent
 *
 * @method void register(string $event_name, string $class, string $method)           static
 * @method void registerWithObj(string $event_name, object $class, string $method)    static
 * @method void on(string $event_name, \Closure $closure)                             static
 * @method void listen(string $event_name, &$data = null, ?\Closure $callback = null) static
 * @method void trigger(string $event_name, ...$params)                               static
 * @method int  size(string $event_name)                                              static
 * @method void delete(string $event_name)                                            static
 * @method bool remove(string $event_name, int $index = 0)                            static
 */
class Event extends Facade
{
    protected static function getContainName()
    {
        return 'event';
    }

    protected static function getFacadeClass()
    {
        return core\Event::class;
    }
}

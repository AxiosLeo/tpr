<?php

declare(strict_types=1);

namespace tpr;

/**
 * Class Event.
 *
 * @method void  register(string $event_name, string $class, string $method)        static
 * @method void  registerWithObj(string $event_name, object $class, string $method) static
 * @method void  on(string $event_name, \Closure $closure)                          static
 * @method void  trigger(string $event_name, ...$params)                            static
 * @method int   size(string $event_name)                                           static
 * @method void  delete(string $event_name)                                         static
 * @method bool  remove(string $event_name, int $index = 0)                         static
 * @method array get(string $event_name)                                            static
 */
class Event extends Facade
{
    public static function listen(string $event_name, &$data = null, ?\Closure $callback = null): void
    {
        self::instance()->listen($event_name, $data, $callback);
    }

    protected static function getContainName()
    {
        return 'event';
    }

    protected static function getFacadeClass()
    {
        return self::instance();
    }

    private static function instance(): core\Event
    {
        if (!Container::has('event')) {
            Container::bind('event', core\Event::class);
        }

        return Container::event();
    }
}

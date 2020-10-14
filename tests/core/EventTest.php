<?php

declare(strict_types=1);

namespace tpr\tests\core;

use PHPUnit\Framework\TestCase;
use tpr\Event;

/**
 * @internal
 * @coversNothing
 */
class EventTest extends TestCase
{
    public function testEvent()
    {
        // register with class name
        Event::register('test_event_register', self::class, 'handle');
        Event::listen('test_event_register', $data);
        $this->assertEquals(0, $data);
        Event::listen('test_event_register', $data);
        $this->assertEquals(200, $data);
        Event::delete('test_event_register');

        // register with object
        $object = new self();
        Event::registerWithObj('test_event_register', $object, 'handle');
        $data = null;
        Event::listen('test_event_register', $data);
        $this->assertEquals(0, $data);
        Event::listen('test_event_register', $data);
        $this->assertEquals(200, $data);
        Event::delete('test_event_register');
    }

    public function testTrigger()
    {
        Event::on('test_trigger', function ($param) {
            $this->assertEquals(123, $param);
        });
        Event::trigger('test_trigger', 123);
    }

    public function handle($data)
    {
        return null === $data ? 0 : 200;
    }
}

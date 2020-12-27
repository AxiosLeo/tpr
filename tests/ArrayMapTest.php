<?php

declare(strict_types=1);

namespace tpr\tests;

use PHPUnit\Framework\TestCase;
use tpr\library\ArrayMap;

/**
 * @internal
 * @coversNothing
 */
class ArrayMapTest extends TestCase
{
    public function testSetGet()
    {
        $array = new ArrayMap();
        $array->set('0.test.a.0.b', 'test');
        $this->assertEquals('test', $array->get('0.test.a.0.b'));
    }

    public function testDelete()
    {
        $array = new ArrayMap([
            'a' => 'a',
            'b' => 'b',
            'c' => 'c',
        ]);
        $array->delete('a');
        $this->assertEquals([
            'b' => 'b',
            'c' => 'c',
        ], $array->get());

        unset($array['b']);
        $this->assertEquals([
            'c' => 'c',
        ], $array->get());
    }
}

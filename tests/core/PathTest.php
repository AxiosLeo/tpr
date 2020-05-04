<?php

declare(strict_types=1);

namespace tpr\tests\core;

use PHPUnit\Framework\TestCase;
use tpr\core\Path;

/**
 * @internal
 * @coversNothing
 */
class PathTest extends TestCase
{
    public function testJoin()
    {
        $path = new Path();
        $res  = $path->join(__DIR__, '../../test.json');
        $this->assertEquals(realpath(__DIR__ . '/../../') . '/test.json', $res);
    }
}

<?php

declare(strict_types=1);

namespace tpr\tests\core;

use PHPUnit\Framework\TestCase;
use tpr\Path;

/**
 * @internal
 * @coversNothing
 */
class PathTest extends TestCase
{
    public function testJoin()
    {
        $this->assertEquals(
            realpath(__DIR__ . '/../../') . '/test.json',
            Path::join(__DIR__, '../../test.json')
        );

        $this->assertEquals(
            '/a/b/c',
            Path::join('/a/', 'b/c')
        );
    }
}

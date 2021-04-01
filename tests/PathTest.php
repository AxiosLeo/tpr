<?php

declare(strict_types=1);

namespace tpr\tests;

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
        $is_win = \PHP_SHLIB_SUFFIX === 'dll';

        $this->assertEquals(
            realpath(__DIR__ . '/../../') . \DIRECTORY_SEPARATOR . 'test.json',
            Path::join(__DIR__, '../../test.json')
        );

        $this->assertEquals(
            $is_win ? '\a\b\c' : '/a/b/c',
            Path::join('/a/', 'b/c')
        );

        $this->assertEquals(
            $is_win ? '\a\b' : '/a/b',
            Path::join('/a/', './', 'b/c', '../')
        );

        $this->assertEquals(
            $is_win ? 'a\b\c\d.php' : 'a/b/c/d.php',
            Path::join('a/', './', 'b/c', 'd.php')
        );
    }
}

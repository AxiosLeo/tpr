<?php

declare(strict_types=1);

namespace tpr\tests;

use PHPUnit\Framework\TestCase;
use tpr\Path;

/**
 * @internal
 * @coversNothing
 */
class FilesTest extends TestCase
{
    public function testCopy()
    {
        \tpr\functions\fs\copy(__DIR__, Path::join(__DIR__, '../runtime/tests/'));
        $this->assertTrue(true);
    }
}

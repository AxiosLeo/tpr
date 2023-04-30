<?php

declare(strict_types=1);

namespace tpr\tests;

use PHPUnit\Framework\TestCase;
use tpr\mimey\MimeTypes;

/**
 * @internal
 *
 * @coversNothing
 */
class MimeyTest extends TestCase
{
    public function testMimeyTest()
    {
        $mimes = new MimeTypes();
        $type = 'text/html; charset=utf-8';
        if (strpos($type, ';')) {
            $tmp = explode(';', $type);
            $type = $tmp[0];
            unset($tmp);
        }
        $contentType = $mimes->getExtension(trim($type));
        $this->assertEquals('html', $contentType);
    }
}

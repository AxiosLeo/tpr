<?php

declare(strict_types=1);

namespace tpr\tests;

use PHPUnit\Framework\TestCase;
use tpr\mimey\MimeTypes;

/**
 * @internal
 * @coversNothing
 */
class MimeyTest extends TestCase
{
    public function test1()
    {
        $mimes       = new MimeTypes();
        $type = 'text/html; charset=utf-8';
        if (strpos($type, ';')) {
            $tmp  = explode(';', $type);
            $type = $tmp[0];
            unset($tmp);
        } else {
            $type = $type;
        }
        $contentType = $mimes->getExtension(trim($type));
        var_dump($contentType);
        $this->assertEquals('html', $contentType);
    }
}

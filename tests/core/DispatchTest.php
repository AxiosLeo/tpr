<?php

declare(strict_types=1);

namespace tpr\tests\core;

use PHPUnit\Framework\TestCase;
use tpr\App;
use tpr\Config;
use tpr\core\Dispatch;

/**
 * @internal
 * @coversNothing
 */
class DispatchTest extends TestCase
{
    public function setUp(): void
    {
        App::drive()->getConfig()->controller_rule = '\\tpr\\tests\\core\\DispatchTest';
        parent::setUp();
    }

    public function testExec()
    {
        $dispatch = new Dispatch('app');
        Config::set('app.route_class_name', self::class);
        $res = $dispatch->dispatch('', '', 'doExec');
        $this->assertEquals('exec result', $res);
    }

    public function doExec()
    {
        return 'exec result';
    }
}

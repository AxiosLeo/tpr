<?php

declare(strict_types=1);

namespace tpr\tests;

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
    protected function setUp(): void
    {
        App::drive()->getConfig()->controller_rule = '\\tpr\\tests\\DispatchTest';
        parent::setUp();
    }

    public function testExec()
    {
        $dispatch = new Dispatch('app');
        Config::set('app.route_class_name', self::class);
        $res = $dispatch->dispatch('', '', 'doExec');
        $this->assertEquals('exec result', $res);
    }

    public function testResolvePathInfo()
    {
        $this->assertEquals(['a', 'b', 'c'], $this->resolvePathInfo('/a/b/c'));
        $this->assertEquals(['a', 'b', 'index'], $this->resolvePathInfo('/a/b'));
        $this->assertEquals(['a', 'index', 'index'], $this->resolvePathInfo('/a'));
        $this->assertEquals(['index', 'index', 'index'], $this->resolvePathInfo('/'));
        $this->assertEquals(['index', 'index', 'index'], $this->resolvePathInfo(''));
    }

    public function doExec(): string
    {
        return 'exec result';
    }

    private function resolvePathInfo($path_info): array
    {
        $path_info = path_join('', $path_info);
        $path_info = str_replace('\\', '/', $path_info);
        $tmp       = explode('/', $path_info, 3);
        $path      = [];
        foreach ($tmp as $item) {
            $p      = $item ?: 'index';
            $path[] = $p;
        }

        $module     = $path[0] ?? 'index';
        $controller = $path[1] ?? 'index';
        $action     = $path[2] ?? 'index';

        return [$module, $controller, $action];
    }
}

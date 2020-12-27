<?php

declare(strict_types=1);

namespace tpr\tests;

use PHPUnit\Framework\TestCase;
use tpr\Container;
use tpr\core\request\DefaultRequest;
use tpr\core\Route;

/**
 * @internal
 * @coversNothing
 */
class RouteTest extends TestCase
{
    private ?Route $route = null;

    private array $routes = [
        [
            'path'    => '/',
            'method'  => 'get|post',
            'handler' => '\\tpr\\tests\\core\\RouteTest:routeHandler',
            'intro'   => 'homepage',
        ],
        [
            'path'    => '/test/{:id}/{:title}/foo/{:bar}',
            'method'  => 'all',
            'handler' => 'index/index/index',
            'intro'   => 'has param',
        ],
        [
            'path'    => '/has/**/text/{:name}',
            'method'  => 'post',
            'handler' => 'index/index/index:routeHandler',
            'intro'   => 'ignore part of path',
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();
        Container::bind('request', DefaultRequest::class);
        $this->route = new Route($this->routes);
    }

    public function testOnlyGlobalRoute()
    {
        $route = new Route([
            [
                'path'    => '/***', // *** : ignore string
                'method'  => 'all',
                'handler' => 'index/index/index',
                'intro'   => 'the default route rule when none of the above rules are matched',
            ],
        ]);
        $this->assertEquals(Route::HAS_FOUND, $route->find('/'));
    }

    public function testRoute()
    {
        $request                   = new DefaultRequest();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Container::bindWithObj('request', $request);
        $res = $this->route->find('/test/123/title/foo/bar');
        $this->assertEquals(Route::HAS_FOUND, $res);
    }

    public function testRoute2()
    {
        $request                   = new DefaultRequest();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Container::bindWithObj('request', $request);
        $res = $this->route->find('');
        $this->assertEquals(Route::HAS_FOUND, $res);
    }

    public function testRoute3()
    {
        $request                   = new DefaultRequest();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Container::bindWithObj('request', $request);
        $res = $this->route->find('/has/anythingInHere/text/name');
        $this->assertEquals(Route::NOT_SUPPORTED_METHOD, $res);

        $request                   = new DefaultRequest();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Container::bindWithObj('request', $request);
        $res = $this->route->find('/has/anythingInHere/text/name');
        $this->assertEquals(Route::HAS_FOUND, $res);
    }

    public function testNotFound()
    {
        $request                   = new DefaultRequest();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        Container::bindWithObj('request', $request);
        $res = $this->route->find('/has/anythingInHere/text/name/test');
        $this->assertEquals(Route::NOT_FOUND, $res);
    }
}

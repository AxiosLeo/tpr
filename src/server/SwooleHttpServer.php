<?php

declare(strict_types=1);

namespace tpr\server;

use Composer\Autoload\ClassLoader;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use tpr\Container;
use tpr\core\Dispatch;
use tpr\core\request\SwooleHttpRequest;
use tpr\core\Template;
use tpr\Event;
use tpr\exception\Handler;
use tpr\exception\HttpResponseException;
use tpr\Lang;
use tpr\Path;

class SwooleHttpServer extends ServerHandler
{
    protected $server_name = 'swoole_http_server';

    protected $app_options = [
        'name'      => 'app',
        'debug'     => false,
        'namespace' => 'App',
        'lang'      => 'zh-cn',
        'swoole'    => [
            'mode'          => SWOOLE_BASE,
            'sock_type'     => SWOOLE_SOCK_TCP,
            'listen'        => '0.0.0.0',
            'port'          => 8080,
            'worker_num'    => 4,
            'daemonize'     => false,
            'backlog'       => 128,
            'max_request'   => 100,
            'dispatch_mode' => 1,
        ],
    ];

    /**
     * @var Server
     */
    private $server;

    public function run()
    {
        Handler::init();
        $this->init();
        $this->server->start();
    }

    public function send(HttpResponseException $httpException, Response $response)
    {
        Event::trigger('app_response_before');
        if (!empty($httpException->headers)) {
            // 发送状态码
            $response->setStatusCode($httpException->http_status);
            // 发送头部信息
            foreach ($httpException->headers as $name => $val) {
                $response->setHeader($name, $val);
            }
        }
        $response->end($httpException->result);

        // 监听response_end
        Event::listen('app_response_after', $httpException->result);
    }

    protected function init()
    {
        $config       = $this->options('swoole');
        $this->server = new Server($config['listen'], $config['port'], $config['mode'], $config['sock_type']);
        Container::bindWithObj('swoole_server', $this->server);
        unset($config['listen'], $config['port'], $config['mode'], $config['sock_type']);
        $this->server->set($config);
        Container::bindNXWithObj('lang', new Lang());
        Event::trigger('app_ini_begin');
        $dispatch = new Dispatch($this->options('namespace'));
        Container::bindWithObj('cgi_dispatch', $dispatch);
        Container::bind('template', Template::class);
        Container::bind('response', \tpr\core\Response::class);
        $ClassLoader = new ClassLoader();
        $ClassLoader->addPsr4($this->options('namespace'), Path::app());
        $ClassLoader->register();
        $this->server->on('connect', static function (Server $server, $id) {
            $data = [
                'server' => &$server,
                'id'     => $id,
            ];
            Event::listen('swoole_connect', $data);
        });

        $this->server->on('request', static function (Request $request, Response $response) {
            Event::trigger('swoole_request');
            $request = new SwooleHttpRequest($request);
            Container::bindWithObj('request', $request);
            Event::delete('http_response');
            Event::add('http_response', __CLASS__, 'send', $response);
            Container::get('cgi_dispatch')->run();
            Event::trigger('swoole_response');
        });

        $this->server->on('close', static function ($server, $id) {
            unset($server, $id);
            Event::trigger('swoole_close');
        });
        Event::trigger('app_ini_end');
    }
}

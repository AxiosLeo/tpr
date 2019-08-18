<?php

declare(strict_types=1);

namespace tpr\server;

use Composer\Autoload\ClassLoader;
use Swoole\Server;
use tpr\Container;
use tpr\core\Dispatch;
use tpr\core\request\SwooleTcpRequest;
use tpr\core\Template;
use tpr\Event;
use tpr\exception\Handler;
use tpr\exception\HttpResponseException;
use tpr\Lang;
use tpr\Path;

class SwooleTcpServer extends ServerAbstract
{
    protected $app_options = [
        'name'       => 'app',
        'debug'      => false,
        'namespace'  => 'App\\',
        'cache_time' => 600,
        'lang'       => 'zh-cn',
        'swoole'     => [
            'mode'          => SWOOLE_BASE,
            'sock_type'     => SWOOLE_SOCK_TCP,
            'listen'        => '127.0.0.1',
            'port'          => 9502,
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
        $this->init();
        $this->server->start();
    }

    public function send(HttpResponseException $httpException, Server $server, $id)
    {
        $server->send($id, $httpException->result);
    }

    protected function init()
    {
        Path::check();
        $config       = $this->options('swoole');
        $this->server = new Server($config['listen'], $config['port'], $config['mode'], $config['sock_type']);
        Container::bind('swoole_server', $this->server);
        unset($config['listen'], $config['port'], $config['mode'], $config['sock_type']);
        $this->server->set($config);
        Handler::init();
        Container::bindNX('lang', new Lang());
        Event::trigger('app_ini_begin');
        $dispatch = new Dispatch($this->options('namespace'));
        Container::bind('cgi_dispatch', $dispatch);
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

        $this->server->on('receive', static function (Server $server, $id, $from_id, $data) {
            Event::trigger('swoole_request');
            $request = new SwooleTcpRequest($id, $from_id, $data);
            Container::bind('request', $request);
            Event::delete('http_response');
            Event::add('http_response', __CLASS__, 'send', [$server, $id]);
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

<?php

declare(strict_types=1);

namespace tpr\server;

use Composer\Autoload\ClassLoader;
use Mimey\MimeTypes;
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
use tpr\models\SwooleServerModel;
use tpr\Path;

class SwooleHttpServer extends ServerHandler
{
    protected array $default_server_options = [
        'mode'          => SWOOLE_BASE,
        'sock_type'     => SWOOLE_SOCK_TCP,
        'listen'        => '0.0.0.0',
        'port'          => 8080,
        'worker_num'    => 4,
        'daemonize'     => false,
        'backlog'       => 128,
        'max_request'   => 100,
        'dispatch_mode' => 1,
    ];

    private ?Server $driver = null;

    public function __construct()
    {
        $this->server = new SwooleServerModel($this->default_server_options);
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        Handler::init();

        try {
            $this->init();
            $this->driver->start();
        } catch (\Exception $e) {
            $whoops = new \Whoops\Run();
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler());
            $whoops->register();

            throw $e;
        }
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

    protected function beginSwooleServer()
    {
        $swoole       = $this->server;
        $this->driver = new Server($swoole->listen, $swoole->port, $swoole->mode, $swoole->sock_type);
        Container::bindWithObj('swoole_server', $this->driver);
        $this->driver->set($swoole->toArray());
        Event::trigger('app_ini_begin');
        $dispatch = new Dispatch($this->app->namespace);
        Container::bindWithObj('cgi_dispatch', $dispatch);
        Container::bind('template', Template::class);
        $ClassLoader = new ClassLoader();
        $ClassLoader->addPsr4($this->app->namespace . '\\', Path::app());
        $ClassLoader->register();
        $this->driver->on('connect', static function (Server $server, $id) {
            $data = [
                'server' => &$server,
                'id'     => $id,
            ];
            Event::listen('swoole_connect', $data);
        });
    }

    protected function init()
    {
        $this->beginSwooleServer();
        $this->driver->on('request', static function (Request $request, Response $response) {
            $uri = $request->server['request_uri'];
            $ext = pathinfo($uri, PATHINFO_EXTENSION);

            try {
                if ('' === $ext) {
                    $request = new SwooleHttpRequest($request);
                    Container::bindWithObj('request', $request);
                    Container::bind('response', \tpr\core\Response::class);
                    /**
                     * @var Dispatch $dispatch
                     */
                    $dispatch = Container::get('cgi_dispatch');
                    $dispatch->run();
                    Event::refresh('http_response', __CLASS__, 'send', $response);
                    Container::get('cgi_dispatch')->run();
                } else {
                    $staticFile = Path::join(Path::index(), $request->server['request_uri']);
                    if ('php' === $ext) {
                        $response->setStatusCode(500);
                        $response->end('');
                    } elseif (file_exists($staticFile)) {
                        $mimes     = new MimeTypes();
                        $mime_type = $mimes->getMimeType($ext);
                        $response->header('Content-Type', $mime_type);
                        $response->sendfile($staticFile);
                    } else {
                        $response->setStatusCode(404);
                        $response->end('');
                    }
                }
            } catch (\Exception $e) {
                if (404 === $e->getCode()) {
                    dump($e->getMessage());
                    $response->setStatusCode(404);
                    $response->end('');
                } else {
                    dump($e);
                }
            }
            $response->close();
            Container::delete('request');
            Container::delete('response');
        });
        $this->endSwooleServer();
    }

    protected function endSwooleServer()
    {
        $this->driver->on('close', static function ($server, $id) {
            unset($server, $id);
            Event::trigger('swoole_close');
        });
        Event::trigger('app_ini_end');
    }
}

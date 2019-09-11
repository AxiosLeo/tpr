<?php

declare(strict_types=1);

namespace tpr\server;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Application;
use tpr\Config;
use tpr\Container;
use tpr\core\Dispatch;
use tpr\core\Lang;
use tpr\core\request\DefaultRequest;
use tpr\core\Response;
use tpr\core\Template;
use tpr\Event;
use tpr\exception\Handler;
use tpr\exception\HttpResponseException;
use tpr\Files;
use tpr\Path;

class DefaultServer extends ServerAbstract
{
    protected $server_name = ServerNameEnum::DEFAULT_SERVER;
    protected $app_options = [
        'name'       => 'app',
        'debug'      => false,
        'namespace'  => 'App\\',
        'cache_time' => 600,
        'lang'       => 'zh-cn',
    ];

    public function run()
    {
        $app_namespace = $this->options('namespace');
        Path::check();
        $event = Config::get('event', []);
        if (!empty($event)) {
            Event::import($event);
        }
        Event::trigger('app_begin', $this->app_options);

        $length = \strlen($app_namespace);
        if ('\\' !== $app_namespace[$length - 1]) {
            $app_namespace .= '\\';
            $this->setOption('namespace', $app_namespace);
        }

        $this->init();
        $this->dispatch();
    }

    public function send(HttpResponseException $httpException)
    {
        Event::trigger('app_response_before');
        if (!headers_sent() && !empty($httpException->headers)) {
            // 发送状态码
            http_response_code($httpException->http_status);
            // 发送头部信息
            foreach ($httpException->headers as $name => $val) {
                if (null === $val) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        }
        echo $httpException->result;
        if (\function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }

        // 监听response_end
        Event::listen('app_response_after', $httpException->result);
        unset($httpException->result);
    }

    protected function init()
    {
        Handler::init();
        Container::bindNX('lang', new Lang());
        Event::trigger('app_ini_begin');
        Event::trigger('app_ini_end');
    }

    private function dispatch()
    {
        $ClassLoader = new ClassLoader();
        $ClassLoader->addPsr4($this->options('namespace'), Path::app());
        $ClassLoader->register();
        $mode = \PHP_SAPI == 'cli' ? \PHP_SAPI : 'cgi';
        if ('cgi' == $mode) {
            $this->cgiRunner();
        } elseif ('cli' == $mode) {
            $this->cliRunner();
        }
        Event::trigger('app_end');
    }

    private function cgiRunner()
    {
        $dispatch = new Dispatch($this->options('namespace'));
        Container::import([
            'response'     => Response::class,
            'template'     => Template::class,
            'cgi_dispatch' => $dispatch,
        ]);
        Container::bind('request', DefaultRequest::class);

        Event::add('http_response', $this, 'send');

        $dispatch->run();
    }

    private function cliRunner()
    {
        $cli_config = Config::get('cli', [
            'name'      => 'Command Tools',
            'version'   => '0.0.1',
            'namespace' => '',
        ]);
        $app        = new Application($cli_config['name'], $cli_config['version']);
        $commands   = Files::searchAllFiles(Path::command(), ['php']);
        if (empty($commands)) {
            throw new \Exception("Not have any command file in '" . Path::command() . "'");
        }
        if (empty($cli_config['namespace'])) {
            $cli_config['namespace'] = $this->options('namespace');
        } else {
            $this->setOption('namespace', $cli_config['namespace']);
        }
        Event::trigger('app_load_command');
        foreach ($commands as $file_path => $filename) {
            require_once $file_path;
            $class = $cli_config['namespace'] . str_replace('/', '\\', str_replace(['.php', Path::command()], '', $file_path));
            if (class_exists($class)) {
                $command = new $class();
                $app->add($command);
            } else {
                echo "---------------------------------------------------\n" .
                    CONSOLE_STYLE_BACKGROUND_31 . "Class Not Exist. Please check namespace ! \n" . CONSOLE_STYLE_DEFAULT .
                    CONSOLE_STYLE_BACKGROUND_33 . 'target class => ' . $class . CONSOLE_STYLE_DEFAULT . "\n" .
                    "---------------------------------------------------\n";
                die();
            }
        }
        Event::trigger('app_run_command_before');
        $app->run();
        Event::trigger('app_run_command_after');
    }
}

<?php

declare(strict_types=1);

namespace tpr\server;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Application;
use tpr\command\Make;
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
use tpr\models\CommandLineAppModel;
use tpr\Path;

class DefaultServer extends ServerHandler
{
    public function run()
    {
        Handler::init();
        $app_namespace = $this->app_model->namespace;
        $event         = Config::get('event', []);
        if (!empty($event)) {
            Event::import($event);
        }
        Event::trigger('app_begin', $this->app_model);

        $length = \strlen($app_namespace);
        if ('\\' === $app_namespace[$length - 1]) {
            throw new \InvalidArgumentException(
                'namespace mustn\'t end with a namespace separator "\". ' .
                'now is "' . $app_namespace . '". ' .
                'should be "' . implode('\\', array_filter(explode('\\', $app_namespace))) . '".'
            );
        }

        $this->init();
        $this->dispatch();
    }

    /**
     * run single command.
     *
     * @param null|string $command
     */
    public function exec($command = null)
    {
        $this->cliRunner($command);
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
        Container::bindNXWithObj('lang', new Lang());
        Event::trigger('app_ini_begin');
        Event::trigger('app_ini_end');
    }

    private function dispatch()
    {
        $ClassLoader = new ClassLoader();
        $ClassLoader->addPsr4($this->app_model->namespace . '\\', Path::app());
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
        $dispatch = new Dispatch($this->app_model->namespace);
        Container::import([
            'response'     => Response::class,
            'template'     => Template::class,
            'cgi_dispatch' => $dispatch,
        ]);
        Container::bind('request', DefaultRequest::class);

        Event::add('http_response', $this, 'send');

        $dispatch->run();
    }

    private function cliRunner($command_name = null)
    {
        $cli_model = new CommandLineAppModel($this->app_model->server_options);
        $app       = new Application($cli_model->name, $cli_model->version);
        $app->add(new Make());
        $commands = Files::searchAllFiles(Path::command(), ['php']);
        if ('' === $cli_model->namespace) {
            $cli_model->namespace = $this->app_model->namespace;
        } else {
            $this->app_model->namespace = $cli_model->namespace;
        }
        Event::trigger('app_load_command');
        foreach ($commands as $file_path => $filename) {
            require_once $file_path;
            $tmp   = str_replace(['.php', Path::command()], '', $file_path);
            $tmp   = str_replace('/', '\\', $tmp);
            $class = $cli_model->namespace . '\\' . $tmp;
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
        if ($command_name) {
            $app->setDefaultCommand($command_name, true);
        }
        $app->run();
        Event::trigger('app_run_command_after');
    }
}

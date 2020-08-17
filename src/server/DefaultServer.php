<?php

declare(strict_types=1);

namespace tpr\server;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
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
    /**
     * @throws \Throwable
     */
    public function run()
    {
        Handler::init();
        $app_namespace = $this->app->namespace;
        $event         = Config::get('event', []);
        if (!empty($event)) {
            Event::import($event);
        }
        Event::trigger('app_begin', $this->app);

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
     *
     * @throws \Exception
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

    /**
     * @throws \Throwable
     */
    private function dispatch()
    {
        $ClassLoader = new ClassLoader();
        $ClassLoader->addPsr4($this->app->namespace . '\\', Path::app());
        $ClassLoader->register();
        $mode = \PHP_SAPI == 'cli' ? \PHP_SAPI : 'cgi';
        if ('cgi' == $mode) {
            $this->cgiRunner();
        } elseif ('cli' == $mode) {
            $this->cliRunner();
        }
        Event::trigger('app_end');
    }

    /**
     * @throws \Throwable
     */
    private function cgiRunner()
    {
        Container::bind('request', DefaultRequest::class);
        $dispatch = new Dispatch($this->app->namespace);
        Container::import([
            'response' => Response::class,
            'template' => Template::class,
        ]);
        Container::bindNXWithObj('cgi_dispatch', $dispatch);
        Event::add('http_response', $this, 'send');
        $dispatch->run();
    }

    /**
     * @param string $command_name
     *
     * @throws \Exception
     */
    private function cliRunner(string $command_name = null)
    {
        /**
         * @var Command $command
         */
        $cli_model = new CommandLineAppModel($this->app->server_options);
        if ('' === $cli_model->namespace) {
            $cli_model->namespace = $this->app->namespace;
        } else {
            $this->app->namespace = $cli_model->namespace;
        }
        $app = new Application($cli_model->name, $cli_model->version);
        if (\count($cli_model->commands) > 0) {
            foreach ($cli_model->commands as $class) {
                $command = new $class();
                $app->add($command);
            }
        }
        Event::trigger('app_load_command');
        $command_files = Files::search(Path::command(), ['php']);
        foreach ($command_files as $filepath) {
            require_once $filepath;
            $tmp   = str_replace(['.php', Path::command()], '', $filepath);
            $tmp   = str_replace('/', '\\', $tmp);
            $class = $cli_model->namespace . $tmp;
            if (!class_exists($class)) {
                echo "---------------------------------------------------\n" .
                    CONSOLE_STYLE_BACKGROUND_31 . "Class Not Exist. Please check namespace ! \n" . CONSOLE_STYLE_DEFAULT .
                    CONSOLE_STYLE_BACKGROUND_33 . 'target class => ' . $class . CONSOLE_STYLE_DEFAULT . "\n" .
                    "---------------------------------------------------\n";
                die();
            }
            $command = new $class();
            $app->add($command);
        }

        Event::trigger('app_run_command_before');
        if ($command_name) {
            $app->setDefaultCommand($command_name, true);
        }
        $app->run();
        Event::trigger('app_run_command_after');
    }
}

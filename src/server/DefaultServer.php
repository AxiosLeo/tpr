<?php

declare(strict_types=1);

namespace tpr\server;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use tpr\Config;
use tpr\Container;
use tpr\core\Dispatch;
use tpr\core\request\DefaultRequest;
use tpr\core\Response;
use tpr\Event;
use tpr\exception\Handler;
use tpr\exception\HttpResponseException;
use tpr\Files;
use tpr\Path;

class DefaultServer extends ServerHandler implements ServerInterface
{
    /**
     * @throws \Throwable
     */
    protected function cgi(): void
    {
        Container::bind('request', DefaultRequest::class);
        $dispatch = new Dispatch($this->app->namespace);
        Container::bind('response', Response::class);
        Container::bindNXWithObj('cgi_dispatch', $dispatch);

        try {
            $dispatch->run();
        } catch (HttpResponseException $e) {
            $this->send($e);
        } catch (\Throwable $e) {
            Handler::render($e, Container::response());
        }
    }

    /**
     * @param string $command_name
     *
     * @throws \Exception
     */
    protected function cli(string $command_name = null): void
    {
        /**
         * @var Command $command
         */
        $command_config = [
            'name'      => isset($this->app->server_options['name']) ? $this->app->server_options['name'] : 'Command Tools',
            'version'   => isset($this->app->server_options['version']) ? $this->app->server_options['version'] : '0.0.1',
            'namespace' => $this->app->namespace,
            'commands'  => isset($this->app->server_options['commands']) ? $this->app->server_options['commands'] : [],
        ];

        $app = new Application($command_config['name'], $command_config['version']);
        if (\count($command_config['commands']) > 0) {
            foreach ($command_config['commands'] as $class) {
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
            $class = $command_config['namespace'] . $tmp;
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

    protected function begin(): void
    {
        Event::trigger('app_begin', $this->app);
        Handler::init();
        $length = \strlen($this->app->namespace);
        if ('\\' === $this->app->namespace[$length - 1]) {
            throw new \InvalidArgumentException(
                'namespace mustn\'t end with a namespace separator "\". ' .
                'now is "' . $this->app->namespace . '". ' .
                'should be "' . implode('\\', array_filter(explode('\\', $this->app->namespace))) . '".'
            );
        }
        Config::load(Path::config());
    }

    protected function end(): void
    {
        Event::trigger('app_end', $this->app);
    }

    private function send(HttpResponseException $httpException)
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
        Event::listen('app_response_after', $httpException->result);
        unset($httpException->result);
    }
}

<?php

namespace tpr\core;

use Composer\Autoload\ClassLoader;
use Exception;
use Symfony\Component\Console\Application;
use tpr\Container;
use tpr\Event;
use tpr\exception\Handler;
use tpr\exception\OptionSetErrorException;

class App
{
    private $app_options = [
        'name'       => 'app',
        'debug'      => false,
        'mode'       => 'cgi',
        'namespace'  => 'App\\',
        'cache_time' => 600,
        'lang'       => 'zh-cn',
    ];

    public function __call($name, $arguments)
    {
        unset($arguments);

        return $this->options($name);
    }

    public function app()
    {
        return $this;
    }

    public function setAppOption($key, $value)
    {
        if (!isset($this->app_options[$key])) {
            throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Name);
        }

        if (\gettype($value) !== \gettype($this->app_options[$key])) {
            throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Value_Type);
        }

        $this->app_options[$key] = $value;

        return $this;
    }

    public function run($debug = null)
    {
        if (null !== $debug) {
            $this->setAppOption('debug', $debug);
        }
        $app_namespace = $this->options('namespace');
        \tpr\Path::check();
        $event = \tpr\Config::get('event', []);
        if (!empty($event)) {
            Event::import($event);
        }
        Event::trigger('app_begin', $this->app_options);

        $length = \strlen($app_namespace);
        if ('\\' !== $app_namespace[$length - 1]) {
            $app_namespace .= '\\';
        }

        $this->setAppOption('namespace', $app_namespace);

        $this->init($this->options('name'));

        $ClassLoader = new ClassLoader();
        $this->setAppOption('namespace', $app_namespace);
        $ClassLoader->addPsr4($app_namespace, \tpr\Path::app());
        $ClassLoader->register();
        $mode = \PHP_SAPI == 'cli' ? \PHP_SAPI : 'cgi';
        if ('cgi' == $mode) {
            $this->cgiRunner();
        } elseif ('cli' == $mode) {
            $this->cliRunner();
        }
        Event::trigger('app_end');
    }

    public function options($key)
    {
        if (!isset($this->app_options[$key])) {
            throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Name);
        }

        return $this->app_options[$key];
    }

    private function init($app_name = 'app')
    {
        Container::bindNX('lang', new Lang());
        Event::trigger('app_ini_begin');
        $this->setAppOption('name', $app_name);
        Handler::init();
        Event::trigger('app_ini_end');

        return $this;
    }

    private function cgiRunner()
    {
        $dispatch = new Dispatch($this->options('namespace'));
        Container::import([
            'request'      => Request::class,
            'response'     => Response::class,
            'template'     => Template::class,
            'cgi_dispatch' => $dispatch,
        ]);

        $dispatch->run();
    }

    private function cliRunner()
    {
        $cli_config = \tpr\Config::get('cli', [
            'name'      => 'Command Tools',
            'version'   => '0.0.1',
            'namespace' => '',
        ]);
        $app        = new Application($cli_config['name'], $cli_config['version']);
        $commands   = \tpr\Files::searchAllFiles(\tpr\Path::command(), ['php']);
        if (empty($commands)) {
            throw new Exception("Not have any command file in '" . \tpr\Path::command() . "'");
        }
        if (empty($cli_config['namespace'])) {
            $cli_config['namespace'] = $this->options('namespace');
        }
        Event::trigger('app_load_command');
        foreach ($commands as $file_path => $filename) {
            require_once $file_path;
            $class = $cli_config['namespace'] . str_replace('/', '\\', str_replace(['.php', \tpr\Path::command()], '', $file_path));
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

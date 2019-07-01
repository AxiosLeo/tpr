<?php

namespace tpr\core;

use Composer\Autoload\ClassLoader;
use tpr\Container;
use tpr\exception\Handler;
use tpr\exception\OptionSetErrorException;

class App
{
    private $namespace;

    private $mode;

    /**
     * @var Dispatch
     */
    private $dispatch;

    private $app_options = [
        "name"      => "app",
        "debug"     => false,
        "mode"      => "cgi",
        "namespace" => "App\\"
    ];

    public function app()
    {
        return $this;
    }

    public function setAppOption($key, $value)
    {
        if (!isset($this->app_options[$key])) {
            throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Name);
        }

        if (gettype($value) === gettype($this->app_options[$key])) {
            throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Value_Type);
        }

        $this->app_options[$key] = $value;
        return $this;
    }

    private function options($key)
    {
        if (!isset($this->app_options[$key])) {
            throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Name);
        }
        return $this->app_options[$key];
    }

    public function init($app_name = 'app')
    {
        $this->setAppOption("name", $app_name);
        \tpr\Path::check();
        Container::import([
            'request'  => Request::class,
            'response' => Response::class,
            'template' => Template::class,
        ]);
        Handler::init();

        return $this;
    }

    public function run($app_namespace = 'App\\', $debug = null)
    {
        if (!is_null($debug)) {
            $this->setAppOption("debug", $debug);
        }
        if (is_null(\tpr\App::name())) {
            $this->init();
        }
        $ClassLoader = new ClassLoader();
        $length      = strlen($app_namespace);
        if ('\\' !== $app_namespace[$length - 1]) {
            $app_namespace .= '\\';
        }
        $this->namespace = $app_namespace;
        $ClassLoader->addPsr4($app_namespace, \tpr\Path::app());
        $ClassLoader->register();
        $this->mode = PHP_SAPI == 'cli' ? PHP_SAPI : 'cgi';
        if ('cgi' == $this->mode) {
            $this->dispatch();
        }
    }

    /**
     * @return Dispatch
     */
    public function getDispatch()
    {
        if (is_null($this->dispatch)) {
            $this->dispatch = new Dispatch($this->namespace);
        }

        return $this->dispatch;
    }

    private function dispatch()
    {
        $this->getDispatch()->run();
    }

    public function __call($name, $arguments)
    {
        unset($arguments);
        return $this->options($name);
    }
}

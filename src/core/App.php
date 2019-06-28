<?php

namespace tpr\core;

use Composer\Autoload\ClassLoader;
use tpr\Container;
use tpr\exception\Handler;

class App
{
    private $namespace;

    private $mode;

    /**
     * @var Dispatch
     */
    private $dispatch;

    public function app()
    {
        return $this;
    }

    public function init($app_name = 'app')
    {
        \tpr\App::setAppName($app_name);
        \tpr\Path::check();
        Container::import([
            'request'  => Request::class,
            'response' => Response::class,
            'template' => Template::class,
        ]);
        Handler::init();

        return $this;
    }

    public function run($app_namespace = 'App\\')
    {
        if (is_null(\tpr\App::appName())) {
            $this->init();
        }
        $this->removeHeaders();
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

    public function removeHeaders($headers = [])
    {
        if (empty($headers)) {
            $headers = \tpr\Config::get('app.remove_headers', ['X-Powered-By']);
        }

        if (!headers_sent() && !empty($headers)) {
            foreach ($headers as $header) {
                header_remove($header);
            }
        }
    }

    private function dispatch()
    {
        $this->getDispatch()->run();
    }
}

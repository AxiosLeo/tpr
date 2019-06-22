<?php

namespace tpr\core;

use Composer\Autoload\ClassLoader;
use tpr\exception\Handler;
use tpr\lib\Container;
use tpr\Path;

/**
 * Class App
 *
 * @package tpr\core
 */
final class App
{
    /**
     * @var Container
     */
    private static $container;

    private static $mode;

    private static $namespace;

    /**
     * @var Dispatch
     */
    private static $dispatch;

    /**
     * @return $this
     */
    public function app()
    {
        return $this;
    }

    public function init($app_name = "app")
    {
        \tpr\App::setAppName($app_name);
        Path::check();

        // init contain
        self::$container = Container::instance();
        Container::set([
            "app"    => App::class,
            "config" => Config::class,
            "cache"  => Cache::class
        ]);

        // load config
        $config = Container::instance()->config();
        $config->init();
        Handler::init();
        return $this;
    }

    public function run($app_namespace = "App\\")
    {
        if (is_null(\tpr\App::appName())) {
            $this->init();
        }
        $this->removeHeaders();
        $ClassLoader = new ClassLoader();
        $length      = strlen($app_namespace);
        if ('\\' !== $app_namespace[$length - 1]) {
            $app_namespace .= "\\";
        }
        self::$namespace = $app_namespace;
        $ClassLoader->addPsr4($app_namespace, Path::app());
        $ClassLoader->register();
        self::$mode = PHP_SAPI == 'cli' ? PHP_SAPI : "cgi";
        if (self::$mode == "cgi") {
            $this->dispatch();
        }
    }

    /**
     * @return Dispatch
     */
    public function getDispatch()
    {
        if (is_null(self::$dispatch)) {
            self::$dispatch = new Dispatch(self::$namespace);
        }
        return self::$dispatch;
    }

    public function removeHeaders($headers = [])
    {
        if (empty($headers)) {
            $headers = \tpr\Config::get("app.remove_headers", ["X-Powered-By"]);
        }

        foreach ($headers as $header) {
            header_remove($header);
        }
    }

    private function dispatch()
    {
        $this->getDispatch()->run();
    }
}
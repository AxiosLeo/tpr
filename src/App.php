<?php

namespace tpr;

use tpr\core\App as CoreApp;
use tpr\lib\Facade;

/**
 * Class App
 *
 * @see     CoreApp
 * @package tpr
 * @method CoreApp init($options = []) static
 * @method void run($app_namespace, $debug = true) static
 * @method CoreApp app() static
 * @method void removeHeaders($headers = []) static
 *
 * @static  $app_name private static
 */
final class App extends Facade
{
    private static $app_name;

    private static $app_debug = false;

    protected static function getContainName()
    {
        return "app";
    }

    protected static function getFacadeClass()
    {
        return CoreApp::class;
    }

    public static function debug()
    {
        return self::$app_debug;
    }

    public static function appName()
    {
        return self::$app_name;
    }

    public static function mode($is_debug)
    {
        self::$app_debug = $is_debug ? true : false;
        return self::app();
    }

    public static function setAppName($app_name)
    {
        self::$app_name = $app_name;
        return self::$app_name;
    }
}
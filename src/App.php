<?php

declare(strict_types=1);

namespace tpr;

use tpr\server\DefaultServer;
use tpr\server\ServerHandler;
use tpr\server\SwooleHttpServer;
use tpr\server\SwooleTcpServer;
use tpr\server\WorkermanHttpServer;

/**
 * Class Client.
 *
 * @method DefaultServer       default()       static
 * @method SwooleHttpServer    swooleHttp()    static
 * @method SwooleTcpServer     swooleTcp()     static
 * @method WorkermanHttpServer workermanHttp() static
 */
class App
{
    public static array $server_list = [
        'default'     => DefaultServer::class,
        'swooleHttp'  => SwooleHttpServer::class,
        'swooleTcp'   => SwooleTcpServer::class,
        'workmanHttp' => WorkermanHttpServer::class,
    ];

    private static bool $debug = false;

    private static ServerHandler $handler;

    public static function __callStatic($name, $arguments)
    {
        return self::drive($name);
    }

    public static function debugMode($debug = null)
    {
        if (null === $debug) {
            return self::$debug;
        }
        if (!\is_bool($debug)) {
            throw new \InvalidArgumentException('debug param must be bool type');
        }
        self::$debug = $debug;

        return self::$debug;
    }

    public static function registerServer(string $name, string $class)
    {
        self::$server_list[$name] = $class;
    }

    public static function drive(string $name = null): ServerHandler
    {
        if (null !== $name) {
            if (!isset(self::$server_list[$name])) {
                throw new \InvalidArgumentException('Invalid server name : ' . $name .
                    ' (you can use `' . implode('/', array_keys(self::$server_list)) . '` for server name)');
            }
            Container::bind('app', self::$server_list[$name]);
            self::$handler = Container::app();
        }

        return self::$handler;
    }
}

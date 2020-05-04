<?php

declare(strict_types=1);

namespace tpr;

use tpr\server\DefaultServer;
use tpr\server\ServerAbstract;
use tpr\server\SwooleHttpServer;
use tpr\server\SwooleTcpServer;

/**
 * Class Client.
 *
 * @method DefaultServer    default()    static
 * @method SwooleHttpServer swooleHttp() static
 * @method SwooleTcpServer  swooleTcp()  static
 */
class App
{
    public static $clients = [
        'default'    => DefaultServer::class,
        'swooleHttp' => SwooleHttpServer::class,
        'swooleTcp'  => SwooleTcpServer::class,
    ];

    private static $client;

    private static $debug = false;

    public static function __callStatic($name, $arguments)
    {
        return self::client($name);
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

    /**
     * @param string $name
     * @param string $class
     */
    public static function setClient(string $name, string $class)
    {
        self::$clients[$name] = $class;
    }

    /**
     * @param null $name
     *
     * @throws \Exception
     *
     * @return ServerAbstract
     */
    public static function client($name = null)
    {
        if (null === $name) {
            return self::$client;
        }

        if (null === self::$client) {
            if (isset(self::$clients[$name])) {
                Container::bind('client', self::$clients[$name]);
            } else {
                $tmp = [];
                $n   = 0;
                foreach (self::$clients as $key => $client) {
                    $tmp[$n++] = $key . ' => ' . $client;
                }

                throw new \Exception("Client not Exist. Supported Clients : \n" . implode("\n", $tmp));
            }

            self::$client = Container::get('client');
        }

        return self::$client;
    }
}

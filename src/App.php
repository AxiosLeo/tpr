<?php

declare(strict_types=1);

namespace tpr;

use tpr\client\ClientAbstract;
use tpr\client\DefaultClient;
use tpr\client\SwooleHttpClient;
use tpr\client\SwooleTcpClient;

/**
 * Class Client.
 *
 * @method DefaultClient    default()    static
 * @method SwooleHttpClient swooleHttp() static
 * @method SwooleTcpClient  swooleTcp()  static
 */
class App
{
    public static $clients = [
        'default'    => DefaultClient::class,
        'swooleHttp' => SwooleHttpClient::class,
        'swooleTcp'  => SwooleTcpClient::class,
    ];

    private static $client;

    public static function __callStatic($name, $arguments)
    {
        return self::client($name);
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
     * @return ClientAbstract
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

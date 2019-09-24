<?php

declare(strict_types=1);

namespace tpr\server;

use MyCLabs\Enum\Enum;

/**
 * Class ServerNameEnum.
 *
 * @method ServerNameEnum DEFAULT_SERVER()     static
 * @method ServerNameEnum SWOOLE_HTTP_SERVER() static
 * @method ServerNameEnum SWOOLE_TCP_SERVER()  static
 */
class ServerNameEnum extends Enum
{
    const DEFAULT_SERVER     = 'default';
    const SWOOLE_HTTP_SERVER = 'swoole_http_server';
    const SWOOLE_TCP_SERVER  = 'swoole_tcp_server';
}

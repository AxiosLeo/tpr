<?php

declare(strict_types=1);

namespace tpr\server;

class ServerNameEnum
{
    const DEFAULT_SERVER     = 'default';
    const SWOOLE_HTTP_SERVER = 'swoole_http_server';
    const SWOOLE_TCP_SERVER  = 'swoole_tcp_server';
}

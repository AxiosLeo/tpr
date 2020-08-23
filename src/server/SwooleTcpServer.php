<?php

declare(strict_types=1);

namespace tpr\server;

use tpr\models\SwooleServerModel;

class SwooleTcpServer extends SwooleHttpServer
{
    protected array $default_server_options = [
        'mode'          => SWOOLE_BASE,
        'sock_type'     => SWOOLE_SOCK_TCP,
        'listen'        => '127.0.0.1',
        'port'          => 9502,
        'worker_num'    => 4,
        'daemonize'     => false,
        'backlog'       => 128,
        'max_request'   => 100,
        'dispatch_mode' => 1,
    ];

    public function __construct()
    {
        $this->server = new SwooleServerModel($this->default_server_options);
        parent::__construct();
    }
}

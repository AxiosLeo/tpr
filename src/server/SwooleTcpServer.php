<?php

declare(strict_types = 1);

namespace tpr\server;

use Swoole\Server;

class SwooleTcpServer extends ServerAbstract
{
    protected $app_options = [

    ];

    /**
     * @var Server
     */
    private $server;

    public function run()
    {
    }

    protected function init()
    {
    }
}

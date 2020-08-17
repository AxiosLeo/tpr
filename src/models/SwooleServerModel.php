<?php

declare(strict_types=1);

namespace tpr\models;

use tpr\Model;

class SwooleServerModel extends Model
{
    public int    $mode          = SWOOLE_BASE;
    public int    $sock_type     = SWOOLE_SOCK_TCP;
    public string $listen        = '0.0.0.0';
    public int    $port          = 80;
    public int    $worker_num    = 4;
    public bool   $daemonize     = false;
    public int    $backlog       = 128;
    public int    $max_request   = 100;
    public int    $dispatch_mode = 1;
}

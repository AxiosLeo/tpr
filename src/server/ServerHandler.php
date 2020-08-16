<?php

declare(strict_types=1);

namespace tpr\server;

use tpr\models\AppModel;
use tpr\Path;

/**
 * Class ClientAbstract.
 */
abstract class ServerHandler
{
    protected AppModel     $app;

    public function __construct()
    {
        $this->app = new AppModel();
    }

    abstract public function run();

    public function config(array $config = []): self
    {
        $this->app->unmarshall($config);

        if (isset($config['path'])) {
            Path::configurate($config['path']);
        }

        return $this;
    }

    public function getConfig()
    {
        return $this->app;
    }

    abstract protected function init();
}

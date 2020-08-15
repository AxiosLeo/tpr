<?php

declare(strict_types=1);

namespace tpr\server;

use tpr\models\AppModel;

/**
 * Class ClientAbstract.
 */
abstract class ServerHandler
{
    protected AppModel $app_model;

    public function __construct()
    {
        $this->app_model = new AppModel();
    }

    abstract public function run();

    public function config(array $config = []): self
    {
        $this->app_model->unmarshall($config);

        return $this;
    }

    public function getConfig()
    {
        return $this->app_model;
    }

    abstract protected function init();
}

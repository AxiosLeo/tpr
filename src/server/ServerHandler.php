<?php

declare(strict_types=1);

namespace tpr\server;

use tpr\Config;
use tpr\Model;
use tpr\models\AppModel;
use tpr\Path;

/**
 * Class ClientAbstract.
 */
abstract class ServerHandler
{
    protected AppModel       $app;
    protected ?Model         $server         = null;
    protected array          $server_options = [];

    public function __construct()
    {
        // init app config model
        $this->app = new AppModel();

        // init path model
        Path::configurate();
    }

    abstract public function run();

    public function config(array $config = []): self
    {
        $this->app->unmarshall($config);

        if (isset($config['path'])) {
            Path::configurate($config['path']);
        }
        if (null !== $this->server && isset($config['server_options'])) {
            $this->server->unmarshall($config['server_options']);
        }
        Config::load(Path::config());

        return $this;
    }

    public function getConfig()
    {
        return $this->app;
    }

    abstract protected function init();
}

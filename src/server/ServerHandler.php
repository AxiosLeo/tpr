<?php

declare(strict_types=1);

namespace tpr\server;

use tpr\exception\Handler;
use tpr\Model;
use tpr\models\AppModel;
use tpr\Path;

/**
 * Class ClientAbstract.
 */
abstract class ServerHandler implements ServerInterface
{
    protected AppModel       $app;
    protected ?Model         $server = null;

    public function __construct()
    {
        // init app config model
        $this->app = new AppModel();

        // init path model
        Path::configurate();
    }

    public function run(string $command = null): void
    {
        Handler::init();
        $this->begin();
        $mode = \PHP_SAPI == 'cli' ? \PHP_SAPI : 'cgi';
        if ('cgi' == $mode) {
            $this->cgi();
        } elseif ('cli' == $mode) {
            $this->cli($command);
        }
        $this->end();
    }

    /**
     * @return $this
     */
    public function config(array $config = []): self
    {
        if (!empty($config)) {
            $this->app->unmarshall($config);

            if (isset($config['path'])) {
                Path::configurate($config['path']);
            }
            if (null !== $this->server && isset($config['server_options'])) {
                $this->server->unmarshall($config['server_options']);
            }
        }

        return $this;
    }

    public function getConfig(): AppModel
    {
        return $this->app;
    }

    /**
     * run server on cgi mode.
     */
    abstract protected function cgi(): void;

    /**
     * run server on cli mode.
     */
    abstract protected function cli(string $command_name = null): void;

    /**
     * begin app.
     */
    abstract protected function begin(): void;

    /**
     * end app.
     */
    abstract protected function end(): void;
}

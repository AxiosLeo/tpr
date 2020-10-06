<?php

declare(strict_types=1);

namespace tpr\server;

use tpr\models\AppModel;

interface ServerInterface
{
    public function run(string $command = null): void;

    public function config(array $config = []): self;

    public function getConfig(): AppModel;
}

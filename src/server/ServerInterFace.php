<?php

declare(strict_types=1);

namespace tpr\server;

interface ServerInterFace
{
    public function run();

    public function setOption($key, $value);

    public function options($key = null);
}

<?php

declare(strict_types=1);

namespace tpr\client;

interface ClientInterFace
{
    public function run();

    public function setOption($key, $value);

    public function options($key = null);
}

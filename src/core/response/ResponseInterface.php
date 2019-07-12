<?php

declare(strict_types=1);

namespace tpr\core\response;

interface ResponseInterface
{
    public function output($data = null);

    public function options($key = null, $value = null): array;
}

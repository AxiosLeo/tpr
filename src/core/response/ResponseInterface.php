<?php

namespace tpr\core\response;

interface ResponseInterface
{
    public function output($data = null);

    public function options($key = null, $value = null): array;
}

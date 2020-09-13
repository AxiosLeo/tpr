<?php

declare(strict_types=1);

namespace tpr\core\response;

use tpr\models\ResponseModel;

abstract class ResponseAbstract
{
    public ?ResponseModel $options = null;

    public string $content_type = '';

    abstract public function output($data = null): string;
}

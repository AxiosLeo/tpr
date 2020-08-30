<?php

declare(strict_types=1);

namespace tpr\core\response;

use InvalidArgumentException;

class Text extends ResponseAbstract
{
    public string $content_type = 'text/html';
    protected string $name      = 'text';

    protected array $options = [];

    public function output($data = null)
    {
        if (\is_array($data) || \is_object($data)) {
            throw new InvalidArgumentException('Not Supported Param Type : ' . \gettype($data));
        }

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace tpr\exception;

class ValidateException extends \RuntimeException
{
    private string $prop_name;

    public function __construct(string $prop_name, string $message = '')
    {
        $this->prop_name = $prop_name;
        parent::__construct($message, 400);
    }

    public function getProperTyName(): string
    {
        return $this->prop_name;
    }
}

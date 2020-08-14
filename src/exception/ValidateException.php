<?php

declare(strict_types=1);

namespace tpr\exception;

class ValidateException extends \RuntimeException
{
    private $prop_name;

    public function __construct($prop_name, $message = '')
    {
        $this->prop_name = $prop_name;
        parent::__construct($message, 400, null);
    }

    public function getProperTyName()
    {
        return $this->prop_name;
    }
}

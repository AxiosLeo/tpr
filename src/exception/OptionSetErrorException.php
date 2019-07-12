<?php

declare(strict_types=1);

namespace tpr\exception;

use RuntimeException;

class OptionSetErrorException extends RuntimeException
{
    const Not_Supported_Option_Name = 0;

    const Not_Supported_Option_Value_Type = 1;
    private $error_type                   = [
        'Not Supported Option Name : {name}',
        'Not Supported Option Value Type : {name}',
    ];

    public function __construct($option_key, $type)
    {
        parent::__construct($this->renderMessage($type, $option_key));
    }

    public function renderMessage($type, $name)
    {
        $tmp = $this->error_type[$type];

        return str_replace('{name}', $name, $tmp);
    }
}

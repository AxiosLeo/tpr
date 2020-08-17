<?php

declare(strict_types=1);

namespace tpr\exception;

use RuntimeException;

class ContainerNotExistException extends RuntimeException
{
    public function __construct($name)
    {
        parent::__construct('`' . $name . '` Container is not exist', 0, null);
    }
}

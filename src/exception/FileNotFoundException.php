<?php

namespace tpr\exception;

use Throwable;
use RuntimeException;

class FileNotFoundException extends RuntimeException
{
    public function __construct(string $path, int $code = 404, Throwable $previous = null)
    {
        parent::__construct('File Not Found : ' . $path, $code, $previous);
    }
}

<?php

declare(strict_types=1);

namespace tpr\exception;

use RuntimeException;

class ClassNotExistException extends RuntimeException
{
    protected $class;

    public function __construct($class_name = '', $message = '')
    {
        parent::__construct(!empty($message) ? $message : 'Class Not Exist : ' . $class_name, 404);
        $this->class = $class_name;
    }

    public function getClass()
    {
        return $this->class;
    }
}

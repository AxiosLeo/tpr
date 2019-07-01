<?php

namespace tpr\exception;

use RuntimeException;

class ClassNotExistException extends RuntimeException
{
    protected $class;

    public function __construct($class_name = '', $message = '')
    {
        parent::__construct();
        $this->message = !empty($message) ? $message : 'Class Not Exist : ' . $class_name;
        $this->class   = $class_name;
    }

    public function getClass()
    {
        return $this->class;
    }
}

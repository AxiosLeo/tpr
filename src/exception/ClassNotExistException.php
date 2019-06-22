<?php

namespace tpr\exception;

class ClassNotExistException extends \RuntimeException
{
    protected $class;

    public function __construct($message, $class = '')
    {
        parent::__construct();
        $this->message = $message;
        $this->class   = $class;
    }

    public function getClass()
    {
        return $this->class;
    }
}
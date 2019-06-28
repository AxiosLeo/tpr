<?php

namespace tpr\exception;

class NotAllowResponseType extends \RuntimeException
{
    private $response_type_list = [];
    private $response_type      = '';

    public function __construct(string $response_type = '', array $response_type_list = [])
    {
        $message                  = 'Not Allow Response Type : "' . $response_type . '"';
        $this->response_type_list = $response_type_list;
        $this->response_type      = $response_type;

        parent::__construct($message);
    }
}

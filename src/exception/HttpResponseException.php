<?php

declare(strict_types=1);

namespace tpr\exception;

use RuntimeException;

class HttpResponseException extends RuntimeException
{
    public $result;
    public $http_status;
    public $headers;
    public $msg;

    public function __construct($result = '', $http_status = 200, $msg = '', $headers = [])
    {
        $this->result      = $result;
        $this->http_status = $http_status;
        $this->headers     = $headers;
        $this->msg         = $msg;
        parent::__construct();
    }
}

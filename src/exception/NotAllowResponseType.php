<?php

declare(strict_types=1);

namespace tpr\exception;

use RuntimeException;

class NotAllowResponseType extends RuntimeException
{
    private array  $response_type_list;
    private string $response_type;

    public function __construct(string $response_type = '', array $response_type_list = [])
    {
        $message                  = 'Not Allow Response Type : "' . $response_type . '"';
        $this->response_type_list = $response_type_list;
        $this->response_type      = $response_type;

        parent::__construct($message);
    }

    public function getResponseTypeList(): array
    {
        return $this->response_type_list;
    }

    public function getResponseType(): string
    {
        return $this->response_type;
    }
}

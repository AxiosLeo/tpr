<?php

namespace tpr\response;


class Html extends ResponseAbstract
{
    protected $name = "html";

    protected $options = [
        'params'     => [],
        'views_path' => ""
    ];

    public $content_type = 'text/html';

    public function output($data = null)
    {
        if (is_array($data) || is_object($data)) {
            throw new \InvalidArgumentException("Not Supported Param Type : " . gettype($data));
        }
        return $data;
    }
}
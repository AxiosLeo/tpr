<?php

namespace tpr\response;

class Text extends ResponseAbstract
{
    protected $name = "text";

    protected $options;

    public $content_type = 'text/html';

    public function output($data = null)
    {
        return $data;
    }
}
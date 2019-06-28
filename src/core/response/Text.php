<?php

namespace tpr\core\response;

class Text extends ResponseAbstract
{
    protected $name = 'text';

    protected $options;

    public $content_type = 'text/html';

    public function output($data = null)
    {
        if (is_array($data) || is_object($data)) {
            throw new \InvalidArgumentException('Not Supported Param Type : ' . gettype($data));
        }

        return $data;
    }
}

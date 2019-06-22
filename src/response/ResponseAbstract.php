<?php

namespace tpr\response;

abstract class ResponseAbstract implements ResponseInterface
{
    protected $name;

    protected $request;

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    protected $options = [];

    public $content_type;

    abstract public function output($data = null);

    public function options($key = null, $value = null): array
    {
        if (is_null($key) && is_null($value)) {
            return $this->options;
        }
        if (is_array($key)) {
            $this->options = array_merge($this->options, $key);
        } else {
            $this->options[$key] = $value;
        }
        return $this->options;
    }

    public function getResponseTypeName()
    {
        return $this->name;
    }
}
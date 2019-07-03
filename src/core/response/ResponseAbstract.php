<?php

namespace tpr\core\response;

abstract class ResponseAbstract implements ResponseInterface
{
    public $content_type;
    protected $name;

    protected $request;

    protected $options = [];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    abstract public function output($data = null);

    public function options($key = null, $value = null): array
    {
        if (null === $key && null === $value) {
            return $this->options;
        }
        if (\is_array($key)) {
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

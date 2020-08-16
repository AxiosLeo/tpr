<?php

declare(strict_types=1);

namespace tpr\core\response;

use tpr\core\request\RequestAbstract;

abstract class ResponseAbstract implements ResponseInterface
{
    public string $content_type;

    protected string $name;

    protected RequestAbstract $request;

    protected array $options = [];

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

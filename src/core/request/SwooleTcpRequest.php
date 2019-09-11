<?php

declare(strict_types=1);

namespace tpr\core\request;

/**
 * Class SwooleTcpRequest.
 */
class SwooleTcpRequest extends RequestAbstract
{
    private $id;
    private $from_id;
    private $data;

    public function __construct($id, $from_id, $data)
    {
        $this->id      = $id;
        $this->from_id = $from_id;
        $this->data    = $data;
    }

    public function __call($name, $arguments)
    {
    }

    public function time($format = null, $micro = false)
    {
    }

    public function id()
    {
        return $this->id;
    }

    public function fromId()
    {
        return $this->from_id;
    }

    public function data()
    {
        return $this->data();
    }

    public function routeInfo($routeInfo = null)
    {
        if (null === $routeInfo) {
            return $this->getRequestData('route_info');
        }

        return $this->setRequestData('route_info', $routeInfo);
    }
}

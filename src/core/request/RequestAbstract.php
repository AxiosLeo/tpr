<?php

declare(strict_types = 1);

namespace tpr\core\request;

use tpr\App;

/**
 * Class RequestAbstract
 *
 * @package tpr\core\request
 * @method string method()
 * @method string url()
 * @method string pathInfo()
 * @method mixed param()
 */
abstract class RequestAbstract
{
    private $request_data = [];

    abstract public function __call($name, $arguments);

    abstract public function time($format = null, $micro = false);

    abstract public function routeInfo($routeInfo = null);

    public function token($refresh = false)
    {
        if ($refresh) {
            return $this->refreshToken();
        }

        return $this->getRequestData('token', function () {
            return $this->refreshToken();
        });
    }

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    protected function refreshToken()
    {
        $token = md5(App::client()->name() . uniqid(md5($this->time(true)), true));

        return $this->setRequestData('token', $token);
    }

    /**
     * @param $name
     * @param $callback
     *
     * @return mixed
     */
    protected function getRequestData($name, \Closure $callback = null)
    {
        if (isset($this->request_data[$name])) {
            return $this->request_data[$name];
        }
        if (null !== $callback) {
            return $callback();
        }

        return null;
    }

    protected function setRequestData($name, $value)
    {
        $this->request_data[$name] = $value;

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace tpr\core\request;

use tpr\App;

/**
 * Class RequestAbstract.
 *
 * @method string method()
 * @method string url()
 * @method string pathInfo()
 */
abstract class RequestAbstract
{
    protected array $server_map   = [];
    private array   $request_data = [];

    public function __call($name, $arguments)
    {
        $is = 's' === $name[1] && 'i' === $name[0];
        if (!$is) {
            unset($arguments);
            if (isset($this->server_map[$name])) {
                return $this->server($this->server_map[$name]);
            }

            return null;
        }
        $method = strtoupper(substr($name, 2));

        return $method === $this->method();
    }

    abstract public function time($format = null, $micro = false);

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function param($name = null, $default = null)
    {
        $params = $this->getRequestData('params', function () {
            if ('POST' === $this->method()) {
                $params = $this->post();
            } else {
                $params = $this->put();
            }
            $params = array_merge($params, $this->get());

            return $this->setRequestData('params', $params);
        });

        return isset($params[$name]) ? $params[$name] : $default;
    }

    public function routeInfo($routeInfo = null)
    {
        if (null === $routeInfo) {
            return $this->getRequestData('route_info');
        }

        return $this->setRequestData('route_info', $routeInfo);
    }

    /**
     * @param false $refresh
     *
     * @throws \Exception
     *
     * @return null|mixed
     */
    public function token($refresh = false)
    {
        if ($refresh) {
            return $this->refreshToken();
        }

        return $this->getRequestData('token', function () {
            return $this->refreshToken();
        });
    }

    abstract public function server($name = null);

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    protected function refreshToken()
    {
        $token = md5(App::drive()->getConfig()->name . uniqid(md5($this->time(true)), true));

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

<?php

declare(strict_types=1);

namespace tpr\core\request;

use tpr\App;

/**
 * Class Request.
 *
 * @method string method()
 * @method string env()
 * @method string protocol()
 * @method string host()
 * @method string domain()
 * @method string port()
 * @method string pathInfo()
 * @method string indexFile()
 * @method string userAgent()
 * @method string accept()
 * @method string lang()
 * @method string encoding()
 * @method string query()
 * @method string remotePort()
 * @method bool   isGet()
 * @method bool   isPost()
 * @method bool   isPut()
 * @method bool   isDelete()
 * @method bool   isHead()
 * @method bool   isPatch()
 * @method bool   isOptions()
 */
abstract class RequestAbstract implements RequestInterface
{
    private $request_data = [];

    abstract public function __call($name, $arguments);

    abstract public function url($is_whole = false);

    abstract public function contentType();

    abstract public function param($name = null, $default = null);

    abstract public function input($array, $name = null, $default = null);

    abstract public function get($name = null, $default = null);

    abstract public function post($name = null, $default = null);

    abstract public function put($name = null, $default = null);

    abstract public function delete($name = null, $default = null);

    abstract public function patch($name = null, $default = null);

    abstract public function request($name = null, $default = null);

    abstract public function content();

    abstract public function time($micro = false, $format = null);

    abstract public function server($name = null);

    abstract public function routeInfo($routeInfo = null);

    abstract public function isHttps();

    abstract public function scheme();

    abstract public function header($name = null, $default = null);

    abstract public function file($name = null);

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

<?php

declare(strict_types = 1);

namespace tpr\core\request;

use Swoole\Http\Request;

/**
 * Class SwooleHttpRequest
 *
 * @package tpr\core\request
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
class SwooleHttpRequest extends RequestAbstract implements RequestInterface
{
    private $swoole_request;

    public function __construct(Request $request)
    {
        $this->swoole_request = $request;
    }

    public function __call($name, $arguments)
    {
    }

    public function url($is_whole = false)
    {
    }

    public function contentType()
    {
    }

    public function param($name = null, $default = null)
    {
    }

    public function input($array, $name = null, $default = null)
    {
    }

    public function get($name = null, $default = null)
    {
    }

    public function post($name = null, $default = null)
    {
    }

    public function put($name = null, $default = null)
    {
    }

    public function delete($name = null, $default = null)
    {
    }

    public function patch($name = null, $default = null)
    {
    }

    public function request($name = null, $default = null)
    {
    }

    public function content()
    {
    }

    public function time($format = null, $micro = false)
    {
    }

    public function server($name = null)
    {
    }

    public function routeInfo($routeInfo = null)
    {
    }

    public function isHttps()
    {
    }

    public function scheme()
    {
    }

    public function header($name = null, $default = null)
    {
    }

    public function file($name = null)
    {
    }
}

<?php

namespace tpr;

use tpr\exception\ClassNotExistException;
use tpr\exception\HttpResponseException;
use tpr\response\ResponseAbstract;
use tpr\traits\InstanceTraits;

class Response
{

    use InstanceTraits;

    protected $request;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var ResponseAbstract
     */
    private $response_driver;

    /**
     * @var array
     */
    private $response_options;

    /**
     * @var string
     */
    private $response_type;

    public function __construct()
    {
        $this->request          = Request::instance();
        $this->response_options = Config::get("app.response", []);
        $this->response_type    = Config::get("app.default_return_type", "html");
    }

    public function setResponseType(string $response_type)
    {
        $this->response_type = $response_type;
        return $this;
    }

    public function setResponseOptions(array $options)
    {
        if (!empty($options)) {
            $this->response_options = array_merge($this->response_options, $options);
        }
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getResponseType()
    {
        return $this->response_type;
    }

    /**
     * @return ResponseAbstract
     */
    public function getResponseDriver()
    {
        return $this->response_driver;
    }

    public function setResponseDriver($driver)
    {
        if (is_string($driver)) {
            if (false === strpos($driver, "\\")) {
                $driver = "tpr\\response\\" . ucfirst($driver);
            }
            if (!class_exists($driver)) {
                throw new ClassNotExistException("Class Not Exist : " . $driver);
            }
            $driver = new $driver();
        }
        $this->response_driver = $driver;
        return $this;
    }

    public function setHeaders($key, $value = null)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setResponseOption($key, $value = null)
    {
        $this->response_options[$key] = $value;
        return $this;
    }

    public function response($result = "", $status = 200, $msg = "", $headers = [])
    {

        if (!empty($headers)) {
            $this->headers = array_merge($this->headers, $headers);
        }
        if (App::debug()) {
            $this->setHeaders("x-mode", "debug");
        }

        $result = $this->output($result);
        throw new HttpResponseException($result, $status, $msg, $this->headers);
    }

    public function success($data = [])
    {
        $this->response($data, 200, "success");
    }

    public function error($code = 500, $msg = "error")
    {
        $this->response("", $code, $msg);
    }

    public function output($result)
    {
        if (is_null($this->response_driver)) {
            $this->setResponseDriver($this->response_type);
        }
        $this->setHeaders("Content-Type", $this->response_driver->content_type);
        $this->response_driver->options($this->response_options);
        return $this->response_driver->output($result);
    }
}
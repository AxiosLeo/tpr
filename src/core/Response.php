<?php

declare(strict_types=1);

namespace tpr\core;

use Exception;
use tpr\App;
use tpr\Container;
use tpr\core\request\RequestAbstract;
use tpr\core\response\ResponseAbstract;
use tpr\core\response\ResponseInterface;
use tpr\exception\ClassNotExistException;
use tpr\exception\HttpResponseException;

class Response
{
    protected ?RequestAbstract $request = null;

    private array $headers = [];

    private ?ResponseAbstract $response_driver = null;

    private array $response_options;

    private string $response_type;

    private array $allow_type = [
        'html', 'json', 'jsonp', 'text', 'xml',
    ];

    private array $headersSet = [];

    public function __construct()
    {
        $this->request          = Container::get('request');
        $this->response_options = \tpr\Config::get('app.response', []);
        $this->response_type    = \tpr\Config::get('app.default_return_type', 'html');
    }

    /**
     * @throws Exception
     *
     * @return $this
     */
    public function setResponseType(string $response_type)
    {
        if (!\in_array($response_type, $this->allow_type)) {
            throw new Exception('Not Allow Response Type : "' . $response_type . '"');
        }
        $this->response_type = $response_type;

        return $this;
    }

    /**
     * @return $this
     */
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

    /**
     * @param ResponseInterface $driver
     *
     * @return $this
     */
    public function setResponseDriver($driver)
    {
        if (\is_string($driver)) {
            if (false === strpos($driver, '\\')) {
                $driver = 'tpr\\core\\response\\' . ucfirst($driver);
            }
            if (!class_exists($driver)) {
                throw new ClassNotExistException($driver);
            }
            $driver = new $driver();
        }
        $this->response_driver = $driver;

        return $this;
    }

    /**
     * @param string $key
     * @param null   $value
     *
     * @return $this
     */
    public function setHeaders($key, $value = null)
    {
        $this->headers[$key]                = $value;
        $this->headersSet[strtolower($key)] = $key;

        return $this;
    }

    /**
     * @param null|string $key
     *
     * @return null|array
     */
    public function getHeaders($key = null)
    {
        if (null === $key) {
            return $this->headers;
        }
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
        $lowerKey = strtolower($key);
        if (isset($this->headersSet[$lowerKey])) {
            return $this->headers[$this->headersSet[$lowerKey]];
        }

        return null;
    }

    /**
     * @param string $key
     * @param null   $value
     *
     * @return $this
     */
    public function setResponseOption($key, $value = null)
    {
        $this->response_options[$key] = $value;

        return $this;
    }

    /**
     * @param mixed  $result
     * @param int    $status
     * @param string $msg
     * @param array  $headers
     *
     * @throws Exception
     */
    public function response($result = '', $status = 200, $msg = '', $headers = [])
    {
        if (!empty($headers)) {
            $this->headers = array_merge($this->headers, $headers);
        }
        if (App::debugMode()) {
            $this->setHeaders('x-mode', 'debug');
        }

        $result = $this->output($result);

        throw new HttpResponseException($result, $status, $msg, $this->headers);
    }

    /**
     * @param array $data
     *
     * @throws Exception
     */
    public function success($data = [])
    {
        $this->response($data, 200, 'success');
    }

    /**
     * @param int    $code
     * @param string $msg
     *
     * @throws Exception
     */
    public function error($code = 500, $msg = 'error')
    {
        $this->response('', $code, $msg);
    }

    /**
     * @param null|mixed $result
     *
     * @return mixed
     */
    public function output($result = null)
    {
        if (null === $this->response_driver) {
            $this->setResponseDriver($this->response_type);
        }
        if (!isset($this->headersSet['content-type'])) {
            // use default content-type header if not set
            $this->setHeaders('Content-Type', $this->response_driver->content_type);
        }
        $this->response_driver->options($this->response_options);

        return $this->response_driver->output($result);
    }
}

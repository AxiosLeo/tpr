<?php

namespace tpr;

use tpr\core\Request;
use tpr\core\Response;

abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    private $response;

    private $vars = [];

    private $response_type;

    public function __construct()
    {
        $this->request       = Container::get('request');
        $this->response      = Container::get('response');
        $this->response_type = $this->response->getResponseType();
    }

    protected function setResponseType($response_type = 'json')
    {
        $this->response->setResponseType($response_type);
        $this->response_type = $response_type;

        return $this;
    }

    protected function setResponseOptions($options)
    {
        $this->response->setResponseOptions($options);
    }

    protected function setResponseOption($key, $value = null)
    {
        $this->response->setResponseOption($key, $value);

        return $this;
    }

    protected function getResponseDriver()
    {
        return $this->response->getResponseDriver();
    }

    protected function addHeader($key, $value = null)
    {
        if (is_null($value)) {
            header($key);
        } else {
            header($key . ':' . $value);
        }

        return $this;
    }

    protected function setHeaders($key, $value = null)
    {
        $this->response->setHeaders($key, $value);

        return $this;
    }

    protected function removeHeaders($headers = [])
    {
        if (is_string($headers)) {
            $headers = [$headers];
        }
        App::removeHeaders($headers);
    }

    /**
     * @param array  $result
     * @param int    $status
     * @param string $msg
     * @param array  $headers
     *
     * @return $this
     */
    protected function response($result = [], $status = 200, $msg = '', $headers = [])
    {
        if (is_null($this->response_type)) {
            $this->setResponseType(Config::get('app.default_ajax_return_type', 'json'));
        }
        $this->response->response($result, $status, $msg, $headers);

        return $this;
    }

    /**
     * @param array $data
     */
    protected function success($data = [])
    {
        $this->response($data, 200, 'success');
    }

    /**
     * @param int    $code
     * @param string $msg
     */
    protected function error($code = 500, $msg = 'error')
    {
        $this->response('', $code, $msg);
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    protected function assign($key, $value)
    {
        $this->vars[$key] = $value;

        return $this;
    }

    /**
     * @param string $template
     * @param array  $vars
     *
     * @return string
     */
    protected function fetch($template = '', $vars = [])
    {
        if (!empty($this->vars)) {
            $vars = empty($vars) ? $this->vars : array_merge($vars, $this->vars);
        }
        $this->setResponseType(Config::get('app.default_return_type', 'html'));
        $this->setResponseOption('view_path', $template);
        $this->setResponseOption('params', $vars);

        return Container::response()->output();
    }
}

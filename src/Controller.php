<?php

namespace tpr;

use tpr\core\Template;

abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    protected $vars = [];

    public function __construct()
    {
        $this->request  = Request::instance();
        $this->response = Response::instance();
    }

    protected function setResponseType($driver = 'json', $options = [])
    {
        $this->response->setResponseType($driver, $options);
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

    protected function setResponseOption($key, $value = null)
    {
        $this->response->setResponseOption($key, $value);
        return $this;
    }

    /**
     * @param string $result
     * @param int    $status
     * @param string $msg
     * @param array  $headers
     *
     * @return $this
     */
    protected function response($result = "", $status = 200, $msg = "", $headers = [])
    {
        $this->response->response($result, $status, $msg, $headers);
        return $this;
    }

    protected function success($data = [])
    {
        $this->response($data, 200, "success");
    }

    protected function error($code = 500, $msg = "error")
    {
        $this->response("", $code, $msg);
    }

    protected function assign($key, $value)
    {
        $this->vars[$key] = $value;
        return $this;
    }

    protected function fetch($template = '', $vars = [])
    {
        $dir  = "";
        $file = "";
        if (empty($template)) {
            $dispatch = App::app()->getDispatch();
            $dir      = Path::dir([
                $dispatch->getModuleName(), $dispatch->getControllerName()
            ]);
            $file     = $dispatch->getActionName();
        } else if (false !== strpos($template, ":")) {
            list($dir, $file) = explode(":", $template);
            $dir = Path::format($dir);
        }
        if (!empty($this->vars)) {
            $vars = empty($vars) ? $this->vars : array_merge($vars, $this->vars);
        }
        return Template::instance()->render($dir, $file, $vars);
    }
}
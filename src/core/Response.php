<?php

declare(strict_types=1);

namespace tpr\core;

use Exception;
use tpr\App;
use tpr\core\response\ResponseAbstract;
use tpr\exception\ClassNotExistException;
use tpr\exception\HttpResponseException;
use tpr\models\ResponseModel;

class Response
{
    protected ?ResponseAbstract $driver = null;

    private ResponseModel $options;

    public function __construct()
    {
        $this->options = new ResponseModel(App::drive()->getConfig()->response_config);
    }

    public function getType(): string
    {
        return $this->options->return_type;
    }

    /**
     * @throws Exception
     *
     * @return $this
     */
    public function setType(string $response_type): self
    {
        if (!isset(ResponseModel::$allow_type[$response_type])) {
            throw new Exception('Not Allow Response Type : "' . $response_type . '"');
        }
        $this->options->return_type = $response_type;

        return $this;
    }

    public function options(): ResponseModel
    {
        return $this->options;
    }

    public function addDriver(string $response_type, string $driver): self
    {
        if (class_exists($driver)) {
            throw new ClassNotExistException($driver);
        }
        ResponseModel::$allow_type[$response_type] = $driver;

        return $this;
    }

    public function getHeaders(): array
    {
        return $this->options->headers;
    }

    public function setHeaders(array $headers, bool $cover = false): self
    {
        if ($cover) {
            $this->options->headers         = $headers;
            $this->options->header_name_set = [];
            foreach ($this->options->headers as $key => $val) {
                $this->options->header_name_set[strtolower($key)] = $key;
            }
        } else {
            foreach ($headers as $key => $val) {
                $lower_key = strtolower($key);
                if (isset($this->options->header_name_set[$lower_key])) {
                    unset($this->options->headers[$this->options->header_name_set[$lower_key]]);
                }
                $this->options->header_name_set[$lower_key] = $key;
                $this->options->headers[$key]               = $val;
            }
        }

        return $this;
    }

    public function getHeader(string $key): string
    {
        if (isset($this->options->headers[$key])) {
            return $this->options->headers[$key];
        }
        $lowerKey = strtolower($key);
        if (isset($this->options->header_name_set[$lowerKey])) {
            return $this->options->headers[$this->options->header_name_set[$lowerKey]];
        }

        return '';
    }

    public function setHeader(string $key, string $value)
    {
        $lower_key                                  = strtolower($key);
        $this->options->headers[$key]               = $value;
        $this->options->header_name_set[$lower_key] = $key;

        return $this;
    }

    public function assign(string $key, $value): self
    {
        $this->options->params[$key] = $value;

        return $this;
    }

    public function fetch(string $template = '', array $vars = []): string
    {
        if (!empty($vars)) {
            $this->options->params = array_merge($this->options->params, $vars);
        }
        $this->options->return_type = 'html';
        $this->options->views_path  = $template;

        return $this->output();
    }

    public function response($result = null, int $status = 200, string $msg = '', array $headers = []): void
    {
        if (App::debugMode()) {
            $this->setHeader('x-mode', 'debug');
        }
        if (!empty($headers)) {
            $this->setHeaders($headers);
        }
        if ('html' === $this->options->return_type) {
            $this->options->return_type = App::drive()->getConfig()->default_content_type_ajax;
        }
        $result = $this->output($result);

        throw new HttpResponseException($result, $status, $msg, $this->options->headers);
    }

    /**
     * @param array $data
     *
     * @throws Exception
     */
    public function success($data = []): void
    {
        $this->response($data, 200, 'success');
    }

    /**
     * @param int    $code
     * @param string $msg
     *
     * @throws Exception
     */
    public function error($code = 500, $msg = 'error'): void
    {
        $this->response('', $code, $msg);
    }

    /**
     * @param null|mixed $result
     *
     * @return mixed
     */
    public function output($result = null): string
    {
        if (null === $this->driver) {
            $class        = ResponseModel::$allow_type[$this->options->return_type];
            $this->driver = new $class();
        }
        if (!isset($this->options->header_name_set['content-type'])) {
            // use default content-type header if not set
            $this->setHeader('Content-Type', $this->driver->content_type);
        }
        if (null === $this->driver->options) {
            $this->driver->options = &$this->options;
        }

        return $this->driver->output($result);
    }
}

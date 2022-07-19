<?php

declare(strict_types=1);

namespace tpr\core\request;

use axios\tools\XMLParser;
use tpr\App;
use tpr\Event;
use tpr\models\RouteInfoModel;

abstract class RequestAbstract
{
    protected array $server_map   = [];
    private array   $request_data = [];

    abstract public function time($format = null, $micro = false);

    abstract public function method(): string;

    abstract public function post($name = null, $default = null);

    abstract public function put($name = null, $default = null);

    abstract public function get($name = null, $default = null);

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function param($name = null, $default = null)
    {
        $params     = $this->getRequestData('params', function () {
            $params = [];
            $params = array_merge($params, $this->put(), $this->post(), $this->get());

            return $this->setRequestData('params', $params);
        });

        return $params[$name] ?? $default;
    }

    public function routeInfo(?RouteInfoModel $routeInfo = null)
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
    public function uuid($refresh = false): string
    {
        if ($refresh) {
            return $this->refresh();
        }

        return $this->getRequestData('token', function () {
            return $this->refresh();
        });
    }

    public function isGet(): bool
    {
        return 0 === strcasecmp('get', $this->method());
    }

    public function isPost(): bool
    {
        return 0 === strcasecmp('post', $this->method());
    }

    public function isPut(): bool
    {
        return 0 === strcasecmp('put', $this->method());
    }

    public function isDelete(): bool
    {
        return 0 === strcasecmp('delete', $this->method());
    }

    public function isHead(): bool
    {
        return 0 === strcasecmp('head', $this->method());
    }

    public function isPatch(): bool
    {
        return 0 === strcasecmp('patch', $this->method());
    }

    public function isOptions(): bool
    {
        return 0 === strcasecmp('options', $this->method());
    }

    abstract public function contentType(): string;

    abstract public function content(): string;

    protected function input($array, $name = null, $default = null)
    {
        if (null === $name) {
            return $array;
        }
        $value = $array[$name] ?? $default;
        $data  = ['name' => $name, 'value' => $value];
        Event::listen('filter_request_data', $data);

        return $data['value'];
    }

    protected function parseContent(): array
    {
        $type = $this->contentType();
        if ('json' === $type) {
            $data = (array) json_decode($this->content(), true);
        } elseif ('xml' === $type) {
            $data = XMLParser::decode($this->content());
        } else {
            parse_str($this->content(), $data);
        }

        return $data;
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

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    private function refresh()
    {
        $token = md5(App::drive()->getConfig()->name . uniqid(md5($this->time(true)), true));

        return $this->setRequestData('token', $token);
    }
}

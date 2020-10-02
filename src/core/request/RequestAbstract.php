<?php

declare(strict_types=1);

namespace tpr\core\request;

use tpr\App;
use tpr\Event;
use tpr\library\Helper;
use tpr\models\RouteInfoModel;

abstract class RequestAbstract
{
    protected array $server_map   = [];
    private array   $request_data = [];

    abstract public function time($format = null, $micro = false);

    abstract public function method(): string;

    abstract public function isPost(): bool;

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
        $params = $this->getRequestData('params', function () {
            $params = [];
            $params = array_merge($params, $this->put(), $this->post(), $this->get());

            return $this->setRequestData('params', $params);
        });

        return isset($params[$name]) ? $params[$name] : $default;
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

    protected function input($array, $name = null, $default = null)
    {
        if (null === $name) {
            return $array;
        }
        $value = isset($array[$name]) ? $array[$name] : $default;
        $data  = ['name' => $name, 'value' => $value];
        Event::listen('filter_request_data', $data);

        return $data['value'];
    }

    protected function parseContent()
    {
        $type = $this->contentType();
        if ('json' === $type) {
            $data = (array) json_decode($this->content(), true);
        } elseif ('xml' === $type) {
            $data = Helper::xmlToArray($this->content());
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

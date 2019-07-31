<?php

declare(strict_types=1);

namespace tpr\core\request;

interface RequestInterface
{
    public function __call($name, $arguments);

    public function url($is_whole = false);

    public function contentType();

    public function param($name = null, $default = null);

    public function input($array, $name = null, $default = null);

    public function get($name = null, $default = null);

    public function post($name = null, $default = null);

    public function put($name = null, $default = null);

    public function delete($name = null, $default = null);

    public function patch($name = null, $default = null);

    public function request($name = null, $default = null);

    public function content();

    public function time($micro = false, $format = null);

    public function server($name = null);

    public function routeInfo($routeInfo = null);

    public function isHttps();

    public function scheme();

    public function header($name = null, $default = null);

    public function file($name = null);
}

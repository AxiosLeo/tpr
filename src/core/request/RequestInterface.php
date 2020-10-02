<?php

declare(strict_types=1);

namespace tpr\core\request;

use tpr\models\RouteInfoModel;

interface RequestInterface
{
    public function uuid(): string;

    public function url($is_whole = false): string;

    public function contentType(): string;

    public function query(): string;

    public function param($name = null, $default = null);

    public function get($name = null, $default = null);

    public function post($name = null, $default = null);

    public function put($name = null, $default = null);

    public function request($name = null, $default = null);

    public function content(): string;

    public function time($format = null, $micro = false);

    public function server($name = null);

    public function pathInfo(): string;

    public function routeInfo(?RouteInfoModel $routeInfo = null);

    public function scheme(): string;

    /**
     * @param ?string $name
     * @param ?string $default
     *
     * @return array|string
     */
    public function header($name = null, $default = null);

    /**
     * get upload file.
     *
     * @param null|string $name
     *
     * @return null|\SplFileInfo|\SplFileInfo[]
     */
    public function file($name = null);

    public function method(): string;

    /**
     * get server software name.
     *
     * @example PHP 7.4.8 Development Server
     */
    public function env(): string;

    public function host(): string;

    public function port(): int;

    public function indexFile(): string;

    public function userAgent(): string;

    public function accept(): string;

    public function lang(): string;

    public function encoding(): string;

    public function isHttps(): bool;

    public function isGet(): bool;

    public function isPost(): bool;

    public function isPut(): bool;

    public function isDelete(): bool;

    public function isHead(): bool;

    public function isPatch(): bool;

    public function isOptions(): bool;
}

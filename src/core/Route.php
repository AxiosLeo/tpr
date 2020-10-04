<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\App;
use tpr\Container;
use tpr\library\ArrayMap;
use tpr\models\RouteInfoModel;
use tpr\traits\CacheTrait;

class Route
{
    use CacheTrait;

    const HAS_FOUND            = 0;
    const NOT_FOUND            = 404;
    const NOT_SUPPORTED_METHOD = 405;

    private array           $routes;
    private array           $data;
    private ?RouteInfoModel $route_info = null;

    public function __construct()
    {
        self::$cache_key = 'cache.routes';
        $this->routes    = \tpr\Config::get('routes', []);
        $this->resolve();
    }

    public function getRouteInfo(): ?RouteInfoModel
    {
        return $this->route_info;
    }

    public function find(?string $pathinfo = null): int
    {
        if (null === $pathinfo || '' === $pathinfo) {
            $pathinfo = '/';
        }
        $trace   = $this->resolvePathInfo($pathinfo);
        $data    = $this->data;
        $step    = 0;
        $total   = \count($trace);
        $default = null;
        $params  = [];
        while (true) {
            $curr = $trace[$step];
            if (isset($data[$curr])) {
                $data = $data[$curr];
            } elseif (isset($data['*'])) {
                $data = $data['*'];
                array_push($params, $curr);
            } elseif (isset($data['**'])) {
                $data = $data['**'];
            } elseif (isset($data['***'])) {
                $default = $data['***']['__route'];
                $data    = $data['***'];
            } elseif (null === $default) {
                return self::NOT_FOUND;
            }
            ++$step;
            if ($step === $total) {
                if (isset($data['__route'])) {
                    return $this->hasFound($data['__route'], $pathinfo, $params);
                }
                if (null !== $default) {
                    return $this->hasFound($default, $pathinfo, $params);
                }
            }
        }
    }

    private function resolve()
    {
        if (!App::debugMode()) {
            $cache = $this->cache();
            if (null !== $cache) {
                $this->data = $cache;

                return;
            }
        }
        $array = new ArrayMap([], '$');
        foreach ($this->routes as $route) {
            if (!isset($route['handler'])) {
                throw new \InvalidArgumentException('Invalid route data, missing `handler`. ');
            }
            if (!isset($route['path'])) {
                throw new \InvalidArgumentException('Invalid route data, missing `path`. ');
            }
            $handler = $route['handler'];
            $trace   = $this->resolvePathInfo($route['path']);
            $params  = [];
            foreach ($trace as &$t) {
                $len = \strlen($t);
                if (0 === strpos($t, '{:')) {
                    $param = substr($t, 2, $len - 3);
                    array_push($params, $param);
                    $t = '*';
                } elseif ('*' === $t) {
                    throw new \InvalidArgumentException('Invalid route data, cannot use single `*` in route.path. ');
                }
            }
            $key        = implode('$', $trace);
            $method     = isset($route['method']) ? strtolower($route['method']) : 'all';
            $intro      = isset($route['intro']) ? $route['intro'] : '';
            $key        = $key . '$__route';
            $route_info = [
                'path'    => $route['path'],
                'method'  => $method,
                'handler' => $handler,
                'intro'   => $intro,
                'params'  => $params,
            ];
            if ('all' === $method) {
                $array->set($key . '$all', $route_info);
            } else {
                $tmp = explode('|', $method);
                foreach ($tmp as $m) {
                    $k = $key . '$' . strtolower(trim($m));
                    $array->set($k, $route_info);
                }
            }
        }
        $this->data = $array->all();
        unset($array);
        if (!App::debugMode()) {
            $this->cache($this->data);
        }
    }

    private function resolvePathInfo($path): array
    {
        if ('' === $path || '/' === $path) {
            $trace = ['@'];
        } elseif ('/' !== $path[0]) {
            throw new \InvalidArgumentException("Invalid route path, should be start with '/'. ");
        } else {
            $path  = '@' . $path;
            $trace = explode('/', $path);
        }

        return $trace;
    }

    private function hasFound(array $route, string $pathinfo, array $params): int
    {
        $curr_method = strtolower(Container::request()->method());
        if (isset($route[$curr_method])) {
            $route_info = $route[$curr_method];
        } elseif (isset($route['all'])) {
            $route_info = $route['all'];
        } else {
            return self::NOT_SUPPORTED_METHOD;
        }
        foreach ($route_info['params'] as $i => $p) {
            $_GET[$p] = $params[$i];
        }
        $route_info['pathinfo'] = $pathinfo;
        $route_info['params']   = $params;
        $this->route_info       = new RouteInfoModel($route_info);

        return self::HAS_FOUND;
    }
}

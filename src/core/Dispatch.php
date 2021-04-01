<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\App;
use tpr\Container;
use tpr\Event;
use tpr\exception\HttpResponseException;
use tpr\library\Helper;
use tpr\Path;

final class Dispatch
{
    private static array $mapping = [];
    private string       $app_namespace;
    private string       $module;
    private string       $controller;
    private string       $action;
    private Route        $route;

    public function __construct($app_namespace)
    {
        $this->app_namespace = $app_namespace;
        $this->route         = new Route();
    }

    public function getAppNamespace(): string
    {
        return $this->app_namespace;
    }

    public function getModuleName(): string
    {
        return $this->module;
    }

    public function getControllerName(): string
    {
        return $this->controller;
    }

    public function getActionName(): string
    {
        return $this->action;
    }

    /**
     * @throws \Throwable
     */
    public function run(): void
    {
        $request = Container::request();
        $result  = null;

        try {
            $pathInfo = $request->pathInfo();
            if (isset(self::$mapping[$pathInfo])) {
                $route_info = self::$mapping[$pathInfo];
                $result     = $this->resolveRouteInfo($request, $route_info);
            } else {
                $status = $this->route->find($pathInfo);
                $result = null;

                switch ($status) {
                    case Route::HAS_FOUND:
                        $route_info = $this->route->getRouteInfo();
                        $result     = $this->resolveRouteInfo($request, $route_info);

                        self::$mapping[$pathInfo] = $route_info;

                        break;

                    case Route::NOT_SUPPORTED_METHOD:
                        Container::response()->error(405, 'Not Allowed Method');

                        break;

                    case Route::NOT_FOUND:
                        if (App::drive()->getConfig()->force_route) {
                            Container::response()->error(404, 'Route Not Found');
                        } else {
                            $result = $this->resolve($pathInfo);
                        }
                }
            }

            throw new HttpResponseException($result);
        } catch (HttpResponseException $e) {
            throw $e;
        }
    }

    public function dispatch($module, $controller, $action, array $params = [])
    {
        $this->module     = $module;
        $this->controller = $controller;
        $this->action     = $action;

        Event::trigger('app_cgi_dispatch', $module, $controller, $action);

        // exec controller
        $class = Helper::renderString(App::drive()->getConfig()->controller_rule, [
            'app_namespace' => $this->app_namespace,
            'module'        => $this->module,
            'controller'    => ucfirst($this->controller),
        ]);
        if (!class_exists($class) || !method_exists($class, $this->action)) {
            throw new \RuntimeException('Class or Method Not Exist : ' . $class . ':' . $this->action, 404);
        }
        $class = new $class();

        return \call_user_func_array([$class, $this->action], $params);
    }

    private function resolveRouteInfo($request, $route_info)
    {
        $request->routeInfo($route_info);
        $tmp              = explode('/', $route_info->handler, 3);
        $this->module     = isset($tmp[0]) ? $tmp[0] : 'index';
        $this->controller = isset($tmp[1]) ? $tmp[2] : 'index';
        $this->action     = isset($tmp[2]) ? $tmp[2] : 'index';

        return $this->dispatch($this->module, $this->controller, $this->action, $route_info['params']);
    }

    private function resolve($path_info)
    {
        if (null !== $path_info) {
            $path_info = Path::join('', $path_info);
            $tmp       = explode('/', $path_info, 3);
            $path      = [];
            foreach ($tmp as $item) {
                $p      = $item ?: 'index';
                $path[] = $p;
            }
        }
        $module     = isset($path[0]) ? $path[0] : 'index';
        $controller = isset($path[1]) ? $path[1] : 'index';
        $action     = isset($path[2]) ? $path[2] : 'index';

        return $this->dispatch($module, $controller, $action);
    }
}

<?php

declare(strict_types=1);

namespace tpr\core;

use Exception;
use tpr\Container;
use tpr\Event;
use tpr\exception\ClassNotExistException;
use tpr\exception\Handler;
use tpr\exception\HttpResponseException;
use tpr\library\Helper;
use tpr\Path;
use tpr\traits\CacheTrait;

class Dispatch
{
    use CacheTrait;

    private string $app_namespace;
    private string $module;
    private string $controller;
    private string $action;
    private string $cache_file;
    private Route  $route;

    private static string $defaultRouteClassName = '{app_namespace}\\{module}\\controller\\{controller}';

    public function __construct($app_namespace)
    {
        $this->app_namespace = $app_namespace;
        $this->cache_file    = Path::join(Path::cache(), 'routes.cache');
        $this->route         = new Route();
    }

    /**
     * @throws \Throwable
     */
    public function run()
    {
        $request = Container::request();
        $result  = null;

        try {
            $pathInfo = $request->pathInfo();
            $status   = $this->route->find($pathInfo);
            $result   = null;
            switch ($status) {
                case Route::HAS_FOUND:

                    $route_info       = $this->route->getRouteInfo();
                    $tmp              = explode('/', $route_info->handler, 3);
                    $this->module     = isset($tmp[0]) ? $tmp[0] : 'index';
                    $this->controller = isset($tmp[1]) ? $tmp[2] : 'index';
                    $this->action     = isset($tmp[2]) ? $tmp[2] : 'index';
                    $this->dispatch($this->module, $this->controller, $this->action, $route_info['params']);

                    break;
                case Route::NOT_SUPPORTED_METHOD:

                    Container::response()->error(405, 'Not Allowed Method');

                    break;
                case Route::NOT_FOUND:

                    if (\tpr\Config::get('app.force_route', false)) {
                        Container::response()->error(404, 'Route Not Found');
                    } else {
                        $this->defaultRoute($pathInfo);
                    }
            }
            $result   = $this->defaultRoute($pathInfo);
            $response = Container::response();
            $response->response($result);
        } catch (HttpResponseException $e) {
            Event::listen('http_response', $e);
        } catch (Exception $e) {
            Handler::render($e, Container::response());
        }
    }

    public function getAppNamespace()
    {
        return $this->app_namespace;
    }

    public function getModuleName()
    {
        return $this->module;
    }

    public function getControllerName()
    {
        return $this->controller;
    }

    public function getActionName()
    {
        return $this->action;
    }

    public function dispatch($module, $controller, $action, array $params = [])
    {
        $this->module     = $module;
        $this->controller = $controller;
        $this->action     = $action;
        $template         = \tpr\Config::get('app.route_class_name', self::$defaultRouteClassName);

        $class = Helper::renderString($template, [
            'app_namespace' => $this->app_namespace,
            'module'        => $this->module,
            'controller'    => ucfirst($this->controller),
        ]);

        return $this->exec($class, $action, $params);
    }

    private function defaultRoute($path_info)
    {
        if (null !== $path_info) {
            $tmp  = explode('/', $path_info);
            $path = [];
            foreach ($tmp as $item) {
                $p = $item ? $item : 'index';
                array_push($path, $p);
            }
        }
        $module     = isset($path[0]) ? $path[0] : 'index';
        $controller = isset($path[1]) ? $path[1] : 'index';
        $action     = isset($path[2]) ? $path[2] : 'index';

        return $this->dispatch($module, $controller, $action);
    }

    private function exec($class, $action, array $vars = [])
    {
        if (!class_exists($class)) {
            throw new ClassNotExistException('Class Not Exist : ' . $class, $class);
        }
        $class = new $class();

        return \call_user_func_array([$class, $action], $vars);
    }
}

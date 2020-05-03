<?php

declare(strict_types=1);

namespace tpr\core;

use Exception;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use tpr\App;
use tpr\Container;
use tpr\Event;
use tpr\exception\ClassNotExistException;
use tpr\exception\Handler;
use tpr\exception\HttpResponseException;
use tpr\library\Helper;
use tpr\Path;

class Dispatch
{
    private $app_namespace;
    private $module;
    private $controller;
    private $action;

    private static $defaultRouteClassName = '{app_namespace}\\{module}\\controller\\{controller}';

    public function __construct($app_namespace)
    {
        $this->app_namespace = $app_namespace;
    }

    public function run()
    {
        $dispatcher = new GroupCountBased($this->getRoutes());
        $request    = Container::request();
        $routeInfo  = $dispatcher->dispatch($request->method(), $request->pathInfo());
        $result     = null;
        Event::listen('app_cgi_dispatch', $routeInfo);

        try {
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    if (\tpr\Config::get('app.force_route', true)) {
                        $pathInfo = $request->pathInfo() ?? '';
                        $result   = $this->defaultRoute($pathInfo, $request->param());
                    } else {
                        Container::response()->response('Route Not Found', 404, []);
                    }

                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    Container::response()->response('Not Allowed Method', 405, []);

                    break;
                case Dispatcher::FOUND:
                    $handler              = $routeInfo[1];
                    $vars                 = $routeInfo[2];
                    list($class, $action) = explode('::', $handler);
                    $request->routeInfo($routeInfo);
                    $rule = explode('\\', \tpr\Config::get('app.route_class_name', self::$defaultRouteClassName));
                    $tmp  = explode('\\', $class);
                    foreach ($rule as $i => $str) {
                        if ('{module}' === $str) {
                            $this->module = $tmp[$i];
                        } elseif ('{controller}' === $str) {
                            $this->controller = $tmp[$i];
                        }
                    }
                    $this->action = $action;
                    $result       = $this->exec($class, $action, $vars);

                    break;
            }
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

    public function dispatch($module, $controller, $action, $params = [])
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

    private function getRoutes()
    {
        $route_data = null;
        if (!App::client()->debug()) {
            $route_data = $this->cache();
        }

        if (null === $route_data) {
            $routeCollector = new RouteCollector(
                new Std(),
                new \FastRoute\DataGenerator\GroupCountBased()
            );

            $routes = \tpr\Config::get('routes', []);

            foreach ($routes as $route_name => $route) {
                $routeCollector->addRoute($route['method'], $route['rule'], $route['handler']);
            }
            $route_data = $routeCollector->getData();
            $this->cache($route_data);
        }

        return $route_data;
    }

    private function defaultRoute($path_info, $params)
    {
        $tmp  = explode('/', $path_info);
        $path = [];
        foreach ($tmp as $item) {
            if (!empty($item)) {
                array_push($path, $item);
            }
        }
        $module     = isset($path[0]) ? $path[0] : 'index';
        $controller = isset($path[1]) ? $path[1] : 'index';
        $action     = isset($path[2]) ? $path[2] : 'index';

        return $this->dispatch($module, $controller, $action, $params);
    }

    private function exec($class, $action, $vars)
    {
        if (!class_exists($class)) {
            throw new ClassNotExistException('Class Not Exist : ' . $class, $class);
        }
        $class = new $class();

        return $class->{$action}($vars);
    }

    private function cache($route_data = null)
    {
        $cache_file = Path::cache() . \DIRECTORY_SEPARATOR . '.' . App::client()->name() . \DIRECTORY_SEPARATOR . 'route.cache';
        if (null === $route_data) {
            if (\tpr\Files::exist($cache_file)) {
                return \tpr\Files::readJsonFile($cache_file);
            }

            return null;
        }
        if (!App::client()->debug()) {
            \tpr\Files::save($cache_file, json_encode($route_data));
        }

        return $route_data;
    }
}

<?php

namespace tpr\core;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use FastRoute\Dispatcher\GroupCountBased;
use tpr\exception\ClassNotExistException;
use tpr\exception\Handler;
use tpr\exception\HttpResponseException;
use tpr\lib\Helper;
use tpr\Path;
use tpr\Request;
use tpr\Response;
use tpr\traits\InstanceTraits;

class Dispatch
{
    use InstanceTraits;

    private $app_namespace;
    private $module;
    private $controller;
    private $action;

    public function __construct($app_namespace)
    {
        $this->app_namespace = $app_namespace;
    }

    public function run()
    {
        $dispatcher = new GroupCountBased($this->getRoutes());
        $request    = Request::instance();
        $routeInfo  = $dispatcher->dispatch($request->method(), $request->pathinfo());
        $result     = null;
        try {
            switch ($routeInfo[0]) {
                case Dispatcher::NOT_FOUND:
                    $result = $this->defaultRoute($request->pathinfo(), $request->param());
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    Response::instance()->response("Not Allowed Method", 405, []);
                    break;
                case Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars    = $routeInfo[2];
                    list($class, $action) = explode("::", $handler);
                    $result = $this->exec($class, $action, $vars);
                    break;
            }
            $response = Response::instance();
            $response->response($result);
        } catch (HttpResponseException $e) {
            $e->send();
        } catch (\Exception $e) {
            Handler::render($e, Response::instance());
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
        $template         = \tpr\Config::get("app.route_class_name", "{app_namespace}{module}\\controller\\{controller}");

        $class = Helper::renderString($template, [
            "app_namespace" => $this->app_namespace,
            "module"        => $module,
            "controller"    => $controller
        ]);
        return $this->exec($class, $action, $params);
    }

    private function getRoutes()
    {
        $cache_file = Path::cache() . 'routes.lock';
        $cache_key  = \tpr\App::debug() . ".routes.cache";
        $route_data = [];
        if (!\tpr\App::debug() && file_exists($cache_file)) {
            if (\tpr\Cache::has($cache_key)) {
                $route_data = \tpr\Cache::get($cache_key);
            }
        }

        if (empty($route_data)) {
            $routeCollector = new RouteCollector(
                new Std(), new \FastRoute\DataGenerator\GroupCountBased()
            );

            $routes = \tpr\Config::get("routes");
            foreach ($routes as $route_name => $route) {
                $routeCollector->addRoute($route['method'], $route['rule'], $route['handler']);
            }
            $route_data = $routeCollector->getData();

            if (!\tpr\App::debug()) {
                \tpr\Cache::set($cache_key, $route_data);
                \tpr\Files::save($cache_file, "cache on " . time());
            }
        }

        return $route_data;
    }

    private function defaultRoute($path_info, $params)
    {
        $tmp  = explode("/", $path_info);
        $path = [];
        foreach ($tmp as $item) {
            if (!empty($item)) {
                array_push($path, $item);
            }
        }
        $module     = isset($path[0]) ? $path[0] : "index";
        $controller = isset($path[1]) ? $path[1] : "index";
        $action     = isset($path[2]) ? $path[2] : "index";

        return $this->dispatch($module, $controller, $action, $params);
    }

    private function exec($class, $action, $vars)
    {
        if (!class_exists($class)) {
            throw new ClassNotExistException("Class Not Exist : " . $class, $class);
        }
        $class = new $class();
        return $class->$action($vars);
    }
}
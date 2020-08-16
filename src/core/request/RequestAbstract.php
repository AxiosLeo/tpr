<?php

declare(strict_types=1);

namespace tpr\core\request;

use tpr\App;
use tpr\library\File;

/**
 * Class RequestAbstract.
 *
 * @method string method()
 * @method string url()
 * @method string pathInfo()
 * @method mixed  param()
 */
abstract class RequestAbstract
{
    protected array $server_map = [];
    private array $request_data = [];

    public function __call($name, $arguments)
    {
        $is = 's' === $name[1] && 'i' === $name[0];
        if (!$is) {
            unset($arguments);
            if (isset($this->server_map[$name])) {
                return $this->server($this->server_map[$name]);
            }

            return null;
        }
        $method = strtoupper(substr($name, 2));

        return $method === $this->method();
    }

    abstract public function time($format = null, $micro = false);

    public function routeInfo($routeInfo = null)
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
    public function token($refresh = false)
    {
        if ($refresh) {
            return $this->refreshToken();
        }

        return $this->getRequestData('token', function () {
            return $this->refreshToken();
        });
    }

    abstract public function server($name = null);

    /**
     * @throws \Exception
     *
     * @return mixed
     */
    protected function refreshToken()
    {
        $token = md5(App::drive()->getConfig()->name . uniqid(md5($this->time(true)), true));

        return $this->setRequestData('token', $token);
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

    protected function resolveFiles($requestFiles = [])
    {
        $files = $requestFiles;
        if (!empty($files)) {
            $array = [];
            foreach ($files as $key => $file) {
                if (\is_array($file['name'])) {
                    $item  = [];
                    $keys  = array_keys($file);
                    $count = \count($file['name']);
                    for ($i = 0; $i < $count; ++$i) {
                        if (empty($file['tmp_name'][$i]) || !is_file($file['tmp_name'][$i])) {
                            continue;
                        }
                        $temp['key'] = $key;
                        foreach ($keys as $_key) {
                            $temp[$_key] = $file[$_key][$i];
                        }
                        $item[] = (new File($temp['tmp_name']))->setUploadInfo($temp);
                    }
                    $array[$key] = $item;
                } else {
                    if ($file instanceof File) {
                        $array[$key] = $file;
                    } else {
                        if (empty($file['tmp_name']) || !is_file($file['tmp_name'])) {
                            continue;
                        }
                        $array[$key] = (new File($file['tmp_name']))->setUploadInfo($file);
                    }
                }
            }
            $files = $array;
            unset($array);
        }

        return $files;
    }

    /**
     * @param File[] $files
     * @param string $name
     *
     * @return File|File[]
     */
    protected function getFile($files, $name)
    {
        if (null === $name) {
            return $files;
        }
        if (isset($array[$name])) {
            return $files[$name];
        }
        if (strpos($name, '.')) {
            list($name, $sub) = explode('.', $name);
            if (isset($sub, $array[$name][$sub])) {
                return $files[$name][$sub];
            }
        }
        unset($files);

        return null;
    }
}

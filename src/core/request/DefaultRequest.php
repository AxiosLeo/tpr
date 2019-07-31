<?php

declare(strict_types=1);

namespace tpr\core\request;

use tpr\Event;
use tpr\library\ArrayTool;
use tpr\library\File;
use tpr\library\Helper;

class DefaultRequest extends RequestAbstract
{
    private $server_map = [
        'method'     => 'REQUEST_METHOD',
        'env'        => 'SERVER_SOFTWARE',
        'protocol'   => 'SERVER_PROTOCOL',
        'host'       => 'HTTP_HOST',
        'domain'     => 'SERVER_NAME',
        'port'       => 'SERVER_PORT',
        'pathInfo'   => 'PATH_INFO',
        'indexFile'  => 'SCRIPT_NAME',
        'indexPath'  => 'SCRIPT_FILENAME',
        'userAgent'  => 'HTTP_USER_AGENT',
        'accept'     => 'HTTP_ACCEPT',
        'lang'       => 'HTTP_ACCEPT_LANGUAGE',
        'encoding'   => 'HTTP_ACCEPT_ENCODING',
        'query'      => 'QUERY_STRING',
        'remotePort' => 'REMOTE_PORT',
    ];

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

    public function url($is_whole = false)
    {
        $url = $this->getRequestData('url', function () {
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
                $url = $_SERVER['HTTP_X_REWRITE_URL'];
            } elseif (isset($_SERVER['REQUEST_URI'])) {
                $url = $_SERVER['REQUEST_URI'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
                $url = $_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
            } else {
                $url = '';
            }

            return $this->setRequestData('url', $url);
        });

        return $is_whole ? $this->scheme() . '://' . $this->domain() . $this->indexFile() . $url : $url;
    }

    public function contentType()
    {
        return $this->getRequestData('content_type', function () {
            $mimes       = new \Mimey\MimeTypes();
            $contentType = $this->server('CONTENT_TYPE');
            if ($contentType) {
                if (strpos($contentType, ';')) {
                    $tmp  = explode(';', $contentType);
                    $type = $tmp[0];
                    unset($tmp);
                } else {
                    $type = $contentType;
                }

                $contentType = $mimes->getExtension(trim($type));
                unset($type);
            }
            unset($mimes);

            return $this->setRequestData('content_type', $contentType);
        });
    }

    /**
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function param($name = null, $default = null)
    {
        /** @var ArrayTool $params */
        $params = $this->getRequestData('params', function () {
            $params = ArrayTool::instance();
            if ('POST' === $this->method()) {
                $params->set($this->post());
            } else {
                $params->set($this->put());
            }
            $params->set($this->get());

            return $this->setRequestData('params', $params);
        });

        return $params->get($name, $default);
    }

    public function input($array, $name = null, $default = null)
    {
        if (null === $name) {
            return $array;
        }
        $value = isset($array[$name]) ? $array[$name] : $default;
        $data  = ['name' => $name, 'value' => $value];
        Event::listen('filter_request_data', $data);

        return $data['value'];
    }

    public function get($name = null, $default = null)
    {
        $get = $this->getRequestData('get', function () {
            return $this->setRequestData('get', $_GET);
        });

        return $this->input($get, $name, $default);
    }

    public function post($name = null, $default = null)
    {
        $post = $this->getRequestData('post', function () {
            $type = $this->contentType();
            if ('json' === $type) {
                $post = (array) json_decode($this->contentType(), true);
            } elseif ('xml' === $type) {
                $post = Helper::xmlToArray($this->content());
            } else {
                $post = $_POST;
            }
            unset($mimes, $type);

            return $this->setRequestData('post', $post);
        });

        return $this->input($post, $name, $default);
    }

    public function put($name = null, $default = null)
    {
        $put = $this->getRequestData('put', function () {
            if ('json' === $this->contentType()) {
                $put = (array) json_decode($this->content(), true);
            } else {
                parse_str($this->content(), $put);
            }

            return $this->setRequestData('put', $put);
        });

        return $this->input($put, $name, $default);
    }

    public function delete($name = null, $default = null)
    {
        return $this->put($name, $default);
    }

    public function patch($name = null, $default = null)
    {
        return $this->put($name, $default);
    }

    public function request($name = null, $default = null)
    {
        $request = $this->getRequestData('request', function () {
            return $this->setRequestData('request', $_REQUEST);
        });
        if (null === $name) {
            return $request;
        }

        if (isset($request[$name])) {
            return $request[$name];
        }

        return $default;
    }

    public function content()
    {
        return $this->getRequestData('content', function () {
            return $this->setRequestData('content', file_get_contents('php://input'));
        });
    }

    public function time($micro = false, $format = null)
    {
        $time = $micro ? $this->server('REQUEST_TIME_FLOAT') : $this->server('REQUEST_TIME');

        return null === $format ? $time : date($format, $time);
    }

    public function server($name = null)
    {
        $server = $this->getRequestData('server', function () {
            return $this->setRequestData('server', $_SERVER);
        });
        if (null === $name) {
            return $server;
        }

        if (isset($server[$name])) {
            $value = $server[$name];
            unset($server, $name);

            return $value;
        }

        return null;
    }

    public function routeInfo($routeInfo = null)
    {
        if (null === $routeInfo) {
            return $this->getRequestData('route_info');
        }

        return $this->setRequestData('route_info', $routeInfo);
    }

    public function isHttps()
    {
        return $this->getRequestData('is_https', function () {
            $server   = $this->server();
            $is_https = false;
            if (isset($server['HTTPS']) && ('1' == $server['HTTPS'] || 'on' == strtolower($server['HTTPS']))) {
                $is_https = true;
            } elseif (isset($server['REQUEST_SCHEME']) && 'https' == $server['REQUEST_SCHEME']) {
                $is_https = true;
            } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
                $is_https = true;
            } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && 'https' == $server['HTTP_X_FORWARDED_PROTO']) {
                $is_https = true;
            }

            return $this->setRequestData('is_https', $is_https);
        });
    }

    public function scheme()
    {
        return $this->getRequestData('scheme', function () {
            return $this->setRequestData('scheme', $this->isHttps() ? 'https' : 'http');
        });
    }

    public function header($name = null, $default = null)
    {
        $headers = $this->getRequestData('headers', function () {
            $headers = [];
            if (\function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
            } else {
                $server = $this->server();
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key           = str_replace('_', '-', strtolower(substr($key, 5)));
                        $headers[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $headers['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $headers['content-length'] = $server['CONTENT_LENGTH'];
                }
            }

            return $this->setRequestData('headers', array_change_key_case($headers));
        });

        if (null === $name) {
            return $headers;
        }

        if (\is_string($name)) {
            $name = str_replace('_', '-', strtolower($name));
        }

        return isset($headers[$name]) ? $headers[$name] : $default;
    }

    /**
     * 获取上传文件.
     *
     * @param null|string $name
     *
     * @return File|File[]
     */
    public function file($name = null)
    {
        $files = $this->getRequestData('files', function () {
            $files = $_FILES ?? [];
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

            return $this->setRequestData('files', $files);
        });

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

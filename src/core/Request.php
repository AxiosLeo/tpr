<?php

namespace tpr\core;

use tpr\Container;
use tpr\library\File;

class Request
{
    protected $method;

    /**
     * @var string 域名（含协议和端口）
     */
    protected $domain;

    /**
     * @var string URL地址
     */
    protected $url;

    /**
     * @var string 基础URL
     */
    protected $baseUrl;

    /**
     * @var string 当前执行的文件
     */
    protected $baseFile;

    /**
     * @var string 访问的ROOT地址
     */
    protected $root;

    /**
     * @var string pathinfo
     */
    protected $pathinfo;

    /**
     * @var string pathinfo（不含后缀）
     */
    protected $path;

    /**
     * @var array 当前路由信息
     */
    protected $routeInfo = [];

    /**
     * @var array 请求参数
     */
    protected $param   = [];
    protected $get     = [];
    protected $post    = [];
    protected $request = [];
    protected $route   = [];
    protected $put;
    protected $session = [];
    protected $file    = [];
    protected $cookie  = [];
    protected $server  = [];
    protected $header  = [];

    /**
     * @var array 资源类型
     */
    protected $mimeType = [
        'xml'   => 'application/xml,text/xml,application/x-xml',
        'json'  => 'application/json,text/x-json,application/jsonrequest,text/json',
        'js'    => 'text/javascript,application/javascript,application/x-javascript',
        'css'   => 'text/css',
        'rss'   => 'application/rss+xml',
        'yaml'  => 'application/x-yaml,text/yaml',
        'atom'  => 'application/atom+xml',
        'pdf'   => 'application/pdf',
        'text'  => 'text/plain',
        'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
        'csv'   => 'text/csv',
        'html'  => 'text/html,application/xhtml+xml,*/*',
    ];

    protected $content;

    // 全局过滤规则
    protected $filter;
    // Hook扩展方法
    protected static $hook = [];
    // 绑定的属性
    protected $bind = [];
    // php://input
    protected $input;
    // 请求缓存
    protected $cache;
    // 缓存是否检查
    protected $isCheckCache;

    /**
     * 构造函数.
     *
     * @param array $options 参数
     */
    public function __construct($options = [])
    {
        foreach ($options as $name => $item) {
            if (property_exists($this, $name)) {
                $this->$name = $item;
            }
        }

        // 保存 php://input
        $this->input = file_get_contents('php://input');
    }

    /**
     * 清除 Request 实例.
     *
     * @desc 用于长连接情况下重置每次请求
     */
    public static function clear()
    {
        Container::delete('request');
    }

    /**
     * 创建一个URL请求
     *
     * @param string $uri     URL地址
     * @param string $method  请求类型
     * @param array  $params  请求参数
     * @param array  $cookie
     * @param array  $files
     * @param array  $server
     * @param string $content
     *
     * @return $this
     */
    public static function create($uri, $method = 'GET', $params = [], $cookie = [], $files = [], $server = [], $content = null)
    {
        $server['PATH_INFO']      = '';
        $server['REQUEST_METHOD'] = strtoupper($method);
        $info                     = parse_url($uri);
        if (isset($info['host'])) {
            $server['SERVER_NAME'] = $info['host'];
            $server['HTTP_HOST']   = $info['host'];
        }
        if (isset($info['scheme'])) {
            if ('https' === $info['scheme']) {
                $server['HTTPS']       = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = 80;
            }
        }
        if (isset($info['port'])) {
            $server['SERVER_PORT'] = $info['port'];
            $server['HTTP_HOST']   = $server['HTTP_HOST'] . ':' . $info['port'];
        }
        if (isset($info['user'])) {
            $server['PHP_AUTH_USER'] = $info['user'];
        }
        if (isset($info['pass'])) {
            $server['PHP_AUTH_PW'] = $info['pass'];
        }
        if (!isset($info['path'])) {
            $info['path'] = '/';
        }
        $options                      = [];
        $options[strtolower($method)] = $params;
        $queryString                  = '';
        if (isset($info['query'])) {
            parse_str(html_entity_decode($info['query']), $query);
            if (!empty($params)) {
                $params      = array_replace($query, $params);
                $queryString = http_build_query($params, '', '&');
            } else {
                $params      = $query;
                $queryString = $info['query'];
            }
        } elseif (!empty($params)) {
            $queryString = http_build_query($params, '', '&');
        }
        if ($queryString) {
            parse_str($queryString, $get);
            $options['get'] = isset($options['get']) ? array_merge($get, $options['get']) : $get;
        }

        $server['REQUEST_URI']  = $info['path'] . ('' !== $queryString ? '?' . $queryString : '');
        $server['QUERY_STRING'] = $queryString;
        $options['cookie']      = $cookie;
        $options['param']       = $params;
        $options['file']        = $files;
        $options['server']      = $server;
        $options['url']         = $server['REQUEST_URI'];
        $options['baseUrl']     = $info['path'];
        $options['pathinfo']    = '/' == $info['path'] ? '/' : ltrim($info['path'], '/');
        $options['method']      = $server['REQUEST_METHOD'];
        $options['domain']      = isset($info['scheme']) ? $info['scheme'] . '://' . $server['HTTP_HOST'] : '';
        $options['content']     = $content;
        $instance               = new self($options);
        Container::bind('request', $instance);

        return $instance;
    }

    /**
     * 设置或获取当前包含协议的域名.
     *
     * @param string $domain 域名
     *
     * @return string
     */
    public function domain($domain = null)
    {
        if (!is_null($domain)) {
            $this->domain = $domain;

            return $this;
        } elseif (!$this->domain) {
            $this->domain = $this->scheme() . '://' . $this->host();
        }

        return $this->domain;
    }

    /**
     * 设置或获取当前完整URL 包括QUERY_STRING.
     *
     * @param string|true $url URL地址 true 带域名获取
     *
     * @return string
     */
    public function url($url = null)
    {
        if (!is_null($url) && true !== $url) {
            $this->url = $url;

            return $this;
        } elseif (!$this->url) {
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
                $this->url = $_SERVER['HTTP_X_REWRITE_URL'];
            } elseif (isset($_SERVER['REQUEST_URI'])) {
                $this->url = $_SERVER['REQUEST_URI'];
            } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
                $this->url = $_SERVER['ORIG_PATH_INFO'] . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
            } else {
                $this->url = '';
            }
        }

        return true === $url ? $this->domain() . $this->url : $this->url;
    }

    /**
     * 设置或获取当前URL 不含QUERY_STRING.
     *
     * @param string $url URL地址
     *
     * @return string
     */
    public function baseUrl($url = null)
    {
        if (!is_null($url) && true !== $url) {
            $this->baseUrl = $url;

            return $this;
        } elseif (!$this->baseUrl) {
            $str           = $this->url();
            $this->baseUrl = strpos($str, '?') ? strstr($str, '?', true) : $str;
        }

        return true === $url ? $this->domain() . $this->baseUrl : $this->baseUrl;
    }

    /**
     * 设置或获取当前执行的文件 SCRIPT_NAME.
     *
     * @param string $file 当前执行的文件
     *
     * @return string
     */
    public function baseFile($file = null)
    {
        if (!is_null($file) && true !== $file) {
            $this->baseFile = $file;

            return $this;
        } elseif (!$this->baseFile) {
            $url            = '';
            $this->baseFile = $url;
        }

        return true === $file ? $this->domain() . $this->baseFile : $this->baseFile;
    }

    /**
     * 设置或获取URL访问根地址
     *
     * @param string $url URL地址
     *
     * @return string
     */
    public function root($url = null)
    {
        if (!is_null($url) && true !== $url) {
            $this->root = $url;

            return $this;
        } elseif (!$this->root) {
            $file = $this->baseFile();
            if ($file && 0 !== strpos($this->url(), $file)) {
                $file = str_replace('\\', '/', dirname($file));
            }
            $this->root = rtrim($file, '/');
        }

        return true === $url ? $this->domain() . $this->root : $this->root;
    }

    /**
     * 获取当前请求URL的pathinfo信息（含URL后缀）.
     *
     * @param array|string $pathinfo_fetch
     *
     * @return string
     */
    public function pathinfo($pathinfo_fetch = [])
    {
        if (is_null($this->pathinfo)) {
            // 分析PATHINFO信息
            if (!isset($_SERVER['PATH_INFO'])) {
                if (is_string($pathinfo_fetch)) {
                    $type = $pathinfo_fetch;
                    if (!empty($_SERVER[$type])) {
                        $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ?
                            substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                    }
                } else {
                    foreach ($pathinfo_fetch as $type) {
                        if (!empty($_SERVER[$type])) {
                            $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ?
                                substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                            break;
                        }
                    }
                }
            }
            $this->pathinfo = empty($_SERVER['PATH_INFO']) ? '/' : ltrim($_SERVER['PATH_INFO'], '/');
        }

        return $this->pathinfo;
    }

    /**
     * 获取当前请求URL的pathinfo信息(不含URL后缀).
     *
     * @param $suffix
     *
     * @return string
     */
    public function path($suffix = '')
    {
        if (is_null($this->path)) {
            $path_info = $this->pathinfo();
            if (false === $suffix) {
                // 禁止伪静态访问
                $this->path = $path_info;
            } elseif ($suffix) {
                // 去除正常的URL后缀
                $this->path = preg_replace('/\.(' . ltrim($suffix, '.') . ')$/i', '', $path_info);
            } else {
                // 允许任何后缀访问
                $this->path = preg_replace('/\.' . $this->ext() . '$/i', '', $path_info);
            }
        }

        return $this->path;
    }

    /**
     * 当前URL的访问后缀
     *
     * @return string
     */
    public function ext()
    {
        return pathinfo($this->pathinfo(), PATHINFO_EXTENSION);
    }

    /**
     * 获取当前请求的时间.
     *
     * @param bool $float 是否使用浮点类型
     *
     * @return int|float
     */
    public function time($float = false)
    {
        return $float ? $_SERVER['REQUEST_TIME_FLOAT'] : $_SERVER['REQUEST_TIME'];
    }

    /**
     * 当前请求的资源类型.
     *
     * @return false|string
     */
    public function type()
    {
        $accept = $this->server('HTTP_ACCEPT');
        if (empty($accept)) {
            return false;
        }

        foreach ($this->mimeType as $key => $val) {
            $array = explode(',', $val);
            foreach ($array as $k => $v) {
                if (stristr($accept, $v)) {
                    return $key;
                }
            }
        }

        return false;
    }

    /**
     * 设置资源类型.
     *
     * @param string|array $type 资源类型名
     * @param string       $val  资源类型
     */
    public function mimeType($type, $val = '')
    {
        if (is_array($type)) {
            $this->mimeType = array_merge($this->mimeType, $type);
        } else {
            $this->mimeType[$type] = $val;
        }
    }

    /**
     * 当前的请求类型.
     *
     * @param bool|string $method true 获取原始请求类型
     *
     * @return string
     */
    public function method($method = false)
    {
        if (true === $method) {
            // 获取原始请求类型
            return isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
        } elseif (!$this->method) {
            if (isset($_POST[$method])) {
                $method = strtoupper($_POST[$method]);
                if (in_array($method, ['GET', 'POST', 'DELETE', 'PUT', 'PATCH'])) {
                    $this->method = $method;
                    unset($_POST[$method]);
                    $this->{$this->method}($_POST);
                } else {
                    $this->method = 'POST';
                }
            } elseif (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $this->method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            } else {
                $this->method = isset($this->server['REQUEST_METHOD']) ? $this->server['REQUEST_METHOD'] : $_SERVER['REQUEST_METHOD'];
            }
        }

        return $this->method;
    }

    /**
     * 是否为GET请求
     *
     * @return bool
     */
    public function isGet()
    {
        return 'GET' == $this->method();
    }

    /**
     * 是否为POST请求
     *
     * @return bool
     */
    public function isPost()
    {
        return 'POST' == $this->method();
    }

    /**
     * 是否为PUT请求
     *
     * @return bool
     */
    public function isPut()
    {
        return 'PUT' == $this->method();
    }

    /**
     * 是否为DELTE请求
     *
     * @return bool
     */
    public function isDelete()
    {
        return 'DELETE' == $this->method();
    }

    /**
     * 是否为HEAD请求
     *
     * @return bool
     */
    public function isHead()
    {
        return 'HEAD' == $this->method();
    }

    /**
     * 是否为PATCH请求
     *
     * @return bool
     */
    public function isPatch()
    {
        return 'PATCH' == $this->method();
    }

    /**
     * 是否为OPTIONS请求
     *
     * @return bool
     */
    public function isOptions()
    {
        return 'OPTIONS' == $this->method();
    }

    /**
     * 是否为cli.
     *
     * @return bool
     */
    public function isCli()
    {
        return PHP_SAPI == 'cli';
    }

    /**
     * 是否为cgi.
     *
     * @return bool
     */
    public function isCgi()
    {
        return 0 === strpos(PHP_SAPI, 'cgi');
    }

    /**
     * 获取当前请求的参数.
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function param($name = '', $default = null, $filter = '')
    {
        if (empty($this->param)) {
            $method = $this->method(true);
            // 自动获取请求变量
            switch ($method) {
                case 'POST':
                    $vars = $this->post(false);
                    break;
                case 'PUT':
                case 'DELETE':
                case 'PATCH':
                    $vars = $this->put(false);
                    break;
                default:
                    $vars = [];
            }
            // 当前请求参数和URL地址中的参数合并
            $this->param = array_merge($this->get(false), $vars);
        }
        if (true === $name) {
            // 获取包含文件上传信息的数组
            $file = $this->file();
            $data = is_array($file) ? array_merge($this->param, $file) : $this->param;

            return $this->input($data, '', $default, $filter);
        }

        return $this->input($this->param, $name, $default, $filter);
    }

    /**
     * 设置获取GET参数.
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        if (empty($this->get)) {
            $this->get = $_GET;
        }
        if (is_array($name)) {
            $this->param = [];

            return $this->get = array_merge($this->get, $name);
        }

        return $this->input($this->get, $name, $default, $filter);
    }

    /**
     * 设置获取POST参数.
     *
     * @param string|null  $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        if (empty($this->post)) {
            $content = $this->input;
            if (empty($_POST) && false !== strpos($this->contentType(), 'application/json')) {
                $this->post = (array) json_decode($content, true);
            } else {
                $this->post = $_POST;
            }
        }
        if (is_array($name)) {
            $this->param = [];

            return $this->post = array_merge($this->post, $name);
        }

        return $this->input($this->post, $name, $default, $filter);
    }

    /**
     * 设置获取PUT参数.
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function put($name = '', $default = null, $filter = '')
    {
        if (is_null($this->put)) {
            $content = $this->input;
            if (false !== strpos($this->contentType(), 'application/json')) {
                $this->put = (array) json_decode($content, true);
            } else {
                parse_str($content, $this->put);
            }
        }
        if (is_array($name)) {
            $this->param = [];

            return $this->put = is_null($this->put) ? $name : array_merge($this->put, $name);
        }

        return $this->input($this->put, $name, $default, $filter);
    }

    /**
     * 设置获取DELETE参数.
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function delete($name = '', $default = null, $filter = '')
    {
        return $this->put($name, $default, $filter);
    }

    /**
     * 设置获取PATCH参数.
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function patch($name = '', $default = null, $filter = '')
    {
        return $this->put($name, $default, $filter);
    }

    /**
     * 获取request变量.
     *
     * @param string|null  $name    数据名称
     * @param string       $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function request($name = '', $default = null, $filter = '')
    {
        if (empty($this->request)) {
            $this->request = $_REQUEST;
        }
        if (is_array($name)) {
            $this->param = [];

            return $this->request = array_merge($this->request, $name);
        }

        return $this->input($this->request, $name, $default, $filter);
    }

    /**
     * 获取server参数.
     *
     * @param string|array $name    数据名称
     * @param string       $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function server($name = '', $default = null, $filter = '')
    {
        if (empty($this->server)) {
            $this->server = $_SERVER;
        }
        if (is_array($name)) {
            return $this->server = array_merge($this->server, $name);
        }

        return $this->input($this->server, false === $name ? false : strtoupper($name), $default, $filter);
    }

    /**
     * 获取上传的文件信息.
     *
     * @param string|array $name 名称
     *
     * @return array|File|null
     */
    public function file($name = '')
    {
        if (empty($this->file)) {
            $this->file = isset($_FILES) ? $_FILES : [];
        }
        if (is_array($name)) {
            return $this->file = array_merge($this->file, $name);
        }
        $files = $this->file;
        if (!empty($files)) {
            // 处理上传文件
            $array = [];
            foreach ($files as $key => $file) {
                if (is_array($file['name'])) {
                    $item  = [];
                    $keys  = array_keys($file);
                    $count = count($file['name']);
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
            if (strpos($name, '.')) {
                list($name, $sub) = explode('.', $name);
            }
            if ('' === $name) {
                // 获取全部文件
                return $array;
            } elseif (isset($sub) && isset($array[$name][$sub])) {
                return $array[$name][$sub];
            } elseif (isset($array[$name])) {
                return $array[$name];
            }
        }

        return null;
    }

    /**
     * 设置或者获取当前的Header.
     *
     * @param string|array $name    header名称
     * @param string       $default 默认值
     *
     * @return string
     */
    public function header($name = '', $default = null)
    {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ?: $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));

        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }

    /**
     * 获取变量 支持过滤和默认值
     *
     * @param array        $data    数据源
     * @param string|false $name    字段名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤函数
     *
     * @return mixed
     */
    public function input($data = [], $name = '', $default = null, $filter = '')
    {
        if (false === $name) {
            // 获取原始数据
            return $data;
        }
        $name = (string) $name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $type) = explode('/', $name);
            } else {
                $type = 's';
            }
            // 按.拆分成多维数组进行判断
            foreach (explode('.', $name) as $val) {
                if (isset($data[$val])) {
                    $data = $data[$val];
                } else {
                    // 无输入数据，返回默认值
                    return $default;
                }
            }
            if (is_object($data)) {
                return $data;
            }
        }

        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }

        return $data;
    }

    /**
     * 设置或获取当前的过滤规则.
     *
     * @param mixed $filter 过滤规则
     *
     * @return mixed
     */
    public function filter($filter = null)
    {
        if (is_null($filter)) {
            return $this->filter;
        } else {
            $this->filter = $filter;
        }

        return null;
    }

    protected function getFilter($filter, $default)
    {
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter;
            if (is_string($filter) && false === strpos($filter, '/')) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array) $filter;
            }
        }

        $filter[] = $default;

        return $filter;
    }

    /**
     * 递归过滤给定的值
     *
     * @param mixed $value   键值
     * @param mixed $key     键名
     * @param array $filters 过滤方法+默认值
     */
    private function filterValue(&$value, $key, $filters)
    {
        unset($key);
        $default = array_pop($filters);
        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            } elseif (is_scalar($value)) {
                if (false !== strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }
        $this->filterExp($value);
    }

    /**
     * 过滤表单中的表达式.
     *
     * @param string $value
     */
    public function filterExp(&$value)
    {
        // 过滤查询特殊字符
        if (is_string($value) && preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT LIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
    }

    /**
     * 强制类型转换.
     *
     * @param string $data
     * @param string $type
     *
     * @throws \InvalidArgumentException
     */
    private function typeCast(&$data, $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array) $data;
                break;
            // 数字
            case 'd':
                $data = (int) $data;
                break;
            // 浮点
            case 'f':
                $data = (float) $data;
                break;
            // 布尔
            case 'b':
                $data = (bool) $data;
                break;
            // 字符串
            case 's':
            default:
                if (is_scalar($data)) {
                    $data = (string) $data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
        }
    }

    /**
     * 是否存在某个请求参数.
     *
     * @param string $name       变量名
     * @param string $type       变量类型
     * @param bool   $checkEmpty 是否检测空值
     *
     * @return mixed
     */
    public function has($name, $type = 'param', $checkEmpty = false)
    {
        if (empty($this->$type)) {
            $param = $this->$type();
        } else {
            $param = $this->$type;
        }
        // 按.拆分成多维数组进行判断
        foreach (explode('.', $name) as $val) {
            if (isset($param[$val])) {
                $param = $param[$val];
            } else {
                return false;
            }
        }

        return ($checkEmpty && '' === $param) ? false : true;
    }

    /**
     * 获取指定的参数.
     *
     * @param string|array $name 变量名
     * @param string       $type 变量类型
     *
     * @return mixed|array
     */
    public function only($name, $type = 'param')
    {
        $param = $this->$type();
        if (is_string($name)) {
            $name = explode(',', $name);
        }
        $item = [];
        foreach ($name as $key) {
            if (isset($param[$key])) {
                $item[$key] = $param[$key];
            }
        }

        return $item;
    }

    /**
     * 排除指定参数获取.
     *
     * @param string|array $name 变量名
     * @param string       $type 变量类型
     *
     * @return mixed
     */
    public function except($name, $type = 'param')
    {
        $param = $this->$type();
        if (is_string($name)) {
            $name = explode(',', $name);
        }
        foreach ($name as $key) {
            if (isset($param[$key])) {
                unset($param[$key]);
            }
        }

        return $param;
    }

    /**
     * 当前是否ssl.
     *
     * @return bool
     */
    public function isSsl()
    {
        $server = array_merge($_SERVER, $this->server);
        if (isset($server['HTTPS']) && ('1' == $server['HTTPS'] || 'on' == strtolower($server['HTTPS']))) {
            return true;
        } elseif (isset($server['REQUEST_SCHEME']) && 'https' == $server['REQUEST_SCHEME']) {
            return true;
        } elseif (isset($server['SERVER_PORT']) && ('443' == $server['SERVER_PORT'])) {
            return true;
        } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && 'https' == $server['HTTP_X_FORWARDED_PROTO']) {
            return true;
        }

        return false;
    }

    /**
     * 当前是否Ajax请求
     *
     * @return bool
     */
    public function isAjax()
    {
        $value = $this->server('HTTP_X_REQUESTED_WITH', '', 'strtolower');

        return 'xmlhttprequest' === $value ? true : false;
    }

    /**
     * 当前是否Pjax请求
     *
     * @return bool
     */
    public function isPjax()
    {
        return !is_null($this->server('HTTP_X_PJAX')) ? true : false;
    }

    /**
     * 获取客户端IP地址
     *
     * @param int  $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param bool $adv  是否进行高级模式获取（有可能被伪装）
     *
     * @return mixed
     */
    public function ip($type = 0, $adv = false)
    {
        $type      = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf('%u', ip2long($ip));
        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];

        return $ip[$type];
    }

    /**
     * 检测是否使用手机访问.
     *
     * @return bool
     */
    public function isMobile()
    {
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], 'wap')) {
            return true;
        } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), 'VND.WAP.WML')) {
            return true;
        } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 当前URL地址中的scheme参数.
     *
     * @return string
     */
    public function scheme()
    {
        return $this->isSsl() ? 'https' : 'http';
    }

    /**
     * 当前请求URL地址中的query参数.
     *
     * @return string
     */
    public function query()
    {
        return $this->server('QUERY_STRING');
    }

    /**
     * 当前请求的host.
     *
     * @return string
     */
    public function host()
    {
        return $this->server('HTTP_HOST');
    }

    /**
     * 当前请求URL地址中的port参数.
     *
     * @return int
     */
    public function port()
    {
        return $this->server('SERVER_PORT');
    }

    /**
     * 当前请求 SERVER_PROTOCOL.
     *
     * @return int
     */
    public function protocol()
    {
        return $this->server('SERVER_PROTOCOL');
    }

    /**
     * 当前请求 REMOTE_PORT.
     *
     * @return int
     */
    public function remotePort()
    {
        return $this->server('REMOTE_PORT');
    }

    /**
     * 当前请求 HTTP_CONTENT_TYPE.
     *
     * @return string
     */
    public function contentType()
    {
        $contentType = $this->server('CONTENT_TYPE');
        if ($contentType) {
            if (strpos($contentType, ';')) {
                list($type) = explode(';', $contentType);
            } else {
                $type = $contentType;
            }

            return trim($type);
        }

        return '';
    }

    /**
     * 获取当前请求的路由信息.
     *
     * @param array $route 路由名称
     *
     * @return array|null
     */
    public function routeInfo($route = [])
    {
        if (!empty($route)) {
            $this->routeInfo = $route;
        } else {
            return $this->routeInfo;
        }

        return null;
    }

    /**
     * 设置或者获取当前请求的content.
     *
     * @return string
     */
    public function getContent()
    {
        if (is_null($this->content)) {
            $this->content = $this->input;
        }

        return $this->content;
    }

    /**
     * 获取当前请求的php://input.
     *
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * 生成请求令牌.
     *
     * @param string $name 令牌名称
     * @param mixed  $type 令牌生成方法
     *
     * @return string
     */
    public function token($name = '__token__', $type = 'md5')
    {
        $type  = is_callable($type) ? $type : 'md5';
        $token = call_user_func($type, $_SERVER['REQUEST_TIME_FLOAT']);
        if ($this->isAjax()) {
            header($name . ': ' . $token);
        }

        return $token;
    }
}

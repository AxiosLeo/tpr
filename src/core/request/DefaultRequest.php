<?php

declare(strict_types=1);

namespace tpr\core\request;

class DefaultRequest extends RequestAbstract implements RequestInterface
{
    protected array $server_map = [
        'method'    => 'REQUEST_METHOD',
        'env'       => 'SERVER_SOFTWARE',
        'scheme'    => 'SERVER_PROTOCOL',
        'host'      => 'HTTP_HOST',
        'domain'    => 'SERVER_NAME',
        'port'      => 'SERVER_PORT',
        'pathInfo'  => 'PATH_INFO',
        'indexFile' => 'SCRIPT_NAME',
        'indexPath' => 'SCRIPT_FILENAME',
        'userAgent' => 'HTTP_USER_AGENT',
        'accept'    => 'HTTP_ACCEPT',
        'lang'      => 'HTTP_ACCEPT_LANGUAGE',
        'encoding'  => 'HTTP_ACCEPT_ENCODING',
        'query'     => 'QUERY_STRING',
    ];

    public function url($is_whole = false): string
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

        return $is_whole ? $this->scheme() . '://' . $this->host() . $this->indexFile() . $url : $url;
    }

    public function contentType(): string
    {
        return $this->getRequestData('content_type', function () {
            $mimes       = new \Mimey\MimeTypes();
            $content_type = $this->server('CONTENT_TYPE');
            if ($content_type) {
                if (strpos($content_type, ';')) {
                    $tmp  = explode(';', $content_type);
                    $type = $tmp[0];
                    unset($tmp);
                } else {
                    $type = $content_type;
                }

                $content_type = $mimes->getExtension(trim($type));
                unset($type);
            }
            unset($mimes);

            return $this->setRequestData('content_type', null === $content_type ? '' : $content_type);
        });
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
            $post = array_merge($_POST, $this->parseContent());

            return $this->setRequestData('post', $post);
        });

        return $this->input($post, $name, $default);
    }

    public function put($name = null, $default = null)
    {
        $put = $this->getRequestData('put', function () {
            return $this->setRequestData('put', $this->parseContent());
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

        return $this->input($request, $name, $default);
    }

    public function content(): string
    {
        return $this->getRequestData('content', function () {
            $content = file_get_contents('php://input');
            if (false === $content) {
                $content = '';
            }

            return $this->setRequestData('content', $content);
        });
    }

    public function time($format = null, $micro = false)
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

        if (isset($this->server_map[$name])) {
            $name = $this->server_map[$name];
        }

        if (isset($server[$name])) {
            $value = $server[$name];
            unset($server, $name);

            return $value;
        }

        return '';
    }

    public function isHttps(): bool
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

    public function scheme(): string
    {
        return $this->server('scheme');
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

        $name = strtolower($name);

        return isset($headers[$name]) ? $headers[$name] : $default;
    }

    /**
     * get upload file.
     *
     * @param null|string $name
     *
     * @return null|\SplFileInfo|\SplFileInfo[]
     */
    public function file($name = null)
    {
        $files = $this->getRequestData('files', function () {
            $list = [];
            if (isset($_FILES) && !empty($_FILES)) {
                foreach ($_FILES as $key => $file) {
                    $list[$key] = new \SplFileInfo($file['tmp_name']);
                }
            }

            return $this->setRequestData('files', $list);
        });

        if (null === $name) {
            return $files;
        }

        return isset($files[$name]) ? $files[$name] : null;
    }

    public function query(): string
    {
        return $this->server($this->server_map['query']);
    }

    public function env(): string
    {
        return $this->server($this->server_map['env']);
    }

    public function host(): string
    {
        return $this->server($this->server_map['host']);
    }

    public function port(): int
    {
        return (int) $this->server($this->server_map['port']);
    }

    public function indexFile(): string
    {
        return $this->server($this->server_map['indexFile']);
    }

    public function userAgent(): string
    {
        return $this->server('userAgent');
    }

    public function accept(): string
    {
        return $this->server('accept');
    }

    public function lang(): string
    {
        return $this->server('lang');
    }

    public function encoding(): string
    {
        return $this->server('encoding');
    }

    public function method(): string
    {
        return $this->server('method');
    }

    public function pathInfo(): string
    {
        return (string) $this->server('pathInfo');
    }
}

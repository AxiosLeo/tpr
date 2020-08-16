<?php

declare(strict_types=1);

namespace tpr\core\request;

use Swoole\Http\Request;
use tpr\Event;
use tpr\traits\ParamTrait;

/**
 * Class SwooleHttpRequest.
 *
 * @method string method()
 * @method string protocol()
 * @method string port()
 * @method string pathInfo()
 * @method bool   isGet()
 * @method bool   isPost()
 * @method bool   isPut()
 * @method bool   isDelete()
 * @method bool   isHead()
 * @method bool   isPatch()
 * @method bool   isOptions()
 */
class SwooleHttpRequest extends RequestAbstract implements RequestInterface
{
    use ParamTrait;

    protected array $server_map = [
        'method'   => 'request_method',
        'protocol' => 'server_protocol',
        'port'     => 'server_port',
        'pathInfo' => 'path_info',
    ];
    private Request $swoole_request;

    public function __construct(Request $request)
    {
        $this->swoole_request = $request;
    }

    public function accept()
    {
        return $this->header('accept-language');
    }

    public function host()
    {
        return $this->header('host');
    }

    public function userAgent()
    {
        return $this->header('user-agent');
    }

    public function url($is_whole = false)
    {
        return $this->server('request_uri');
    }

    public function contentType()
    {
        return $this->header('content-type');
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
        return $this->getRequestData('get', function () {
            return $this->setRequestData('get', $this->swoole_request->get);
        });
    }

    public function post($name = null, $default = null)
    {
        return $this->getRequestData('post', function () {
            return $this->setRequestData('post', $this->swoole_request->post);
        });
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
        return $this->getRequestData('request', function () {
            return $this->setRequestData('request', $this->swoole_request);
        });
    }

    public function content()
    {
        return $this->getRequestData('content', function () {
            return $this->setRequestData('content', $this->swoole_request->rawContent());
        });
    }

    public function time($format = null, $micro = false)
    {
        $time = $micro ? $this->server('request_time_float') : $this->server('request_time');

        return null === $format ? $time : date($format, $time);
    }

    public function server($name = null)
    {
        $server = $this->getRequestData('server', function () {
            return $this->setRequestData('server', $this->swoole_request->server);
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
            return $this->setRequestData('is_https', 'https' === $this->scheme());
        });
    }

    public function scheme()
    {
        return $this->getRequestData('scheme', function () {
            return $this->setRequestData(
                'scheme',
                strpos($this->server('server_protocol'), 'HTTP/1.1') ? 'http' : 'https'
            );
        });
    }

    public function header($name = null, $default = null)
    {
        $header = $this->getRequestData('header', function () {
            return $this->setRequestData('header', $this->swoole_request->header);
        });

        if (null === $name) {
            return $header;
        }

        return isset($header[$name]) ? $header[$name] : null;
    }

    public function file($name = null)
    {
        $files = $this->getRequestData('files', function () {
            return $this->setRequestData('files', $this->resolveFiles($this->swoole_request->files));
        });

        return $this->getFile($files, $name);
    }
}

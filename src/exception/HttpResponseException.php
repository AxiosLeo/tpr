<?php

namespace tpr\exception;

use RuntimeException;
use tpr\Event;

class HttpResponseException extends RuntimeException
{
    protected $result;
    protected $http_status;
    protected $headers;
    protected $msg;

    public function __construct($result = '', $http_status = 200, $msg = '', $headers = [])
    {
        $this->result      = $result;
        $this->http_status = $http_status;
        $this->headers     = $headers;
        $this->msg         = $msg;
        parent::__construct();
    }

    public function send()
    {
        Event::trigger('app_response_before');
        if (!headers_sent() && !empty($this->headers)) {
            // 发送状态码
            http_response_code($this->code);
            // 发送头部信息
            foreach ($this->headers as $name => $val) {
                if (null === $val) {
                    header($name);
                } else {
                    header($name . ':' . $val);
                }
            }
        }
        echo $this->result;
        if (\function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }

        // 监听response_end
        Event::listen('app_response_after', $this->result);
        unset($this->result);
    }
}

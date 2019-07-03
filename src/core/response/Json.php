<?php

namespace tpr\core\response;

use Exception;
use InvalidArgumentException;

class Json extends ResponseAbstract
{
    public $content_type = 'application/json';
    protected $name      = 'json';

    protected $options = [
        'json_encode_param' => JSON_UNESCAPED_UNICODE,
    ];

    public function output($data = null): string
    {
        try {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data, $this->options['json_encode_param']);
            if (false === $data) {
                throw new InvalidArgumentException(json_last_error_msg());
            }

            return $data;
        } catch (Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }

            throw $e;
        }
    }
}
<?php

namespace tpr\response;

class Jsonp extends ResponseAbstract
{
    protected $name = "jsonp";

    protected $options = [
        'json_encode_param' => JSON_UNESCAPED_UNICODE,
        'jsonp_handler'     => 'jsonpReturn',
    ];

    public $content_type = 'application/javascript';

    public function output($data = null): string
    {
        try {
            $handler = !empty($var_jsonp_handler) ? $var_jsonp_handler : $this->options['jsonp_handler'];

            $data = json_encode($data, $this->options['json_encode_param']);

            if (false === $data) {
                throw new \InvalidArgumentException(json_last_error_msg());
            }

            $data = $handler . '(' . $data . ');';

            return $data;
        } catch (\Exception $e) {
            if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
            throw $e;
        }
    }
}
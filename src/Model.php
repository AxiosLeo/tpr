<?php

declare(strict_types=1);

namespace tpr;

use Rakit\Validation\Validator;
use tpr\exception\ValidateException;

abstract class Model
{
    protected $_rules    = [];
    protected $_messages = [];
    protected $_alias    = [];

    public function __construct($properties = [])
    {
        $keys = array_keys(get_object_vars($this));
        foreach ($properties as $key => $val) {
            if (\in_array($key, $keys)) {
                $this->{$key} = $val;
            }
        }
        Container::bindNX('validator', Validator::class);
        $validator  = Container::validator();
        $validation = $validator->make(get_object_vars($this), $this->_rules);
        $validation->setAliases($this->_alias);
        $validation->setMessages($this->_messages);
        $validation->validate();
        if ($validation->fails()) {
            $error     = $validation->errors()->firstOfAll();
            $prop_name = array_key_first($error);
            $msg       = $error[$prop_name];

            throw new ValidateException($prop_name, $msg);
        }
        unset($validator, $validation, $properties);
    }
}

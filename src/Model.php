<?php

declare(strict_types=1);

namespace tpr;

use Rakit\Validation\Validation;
use Rakit\Validation\Validator;
use tpr\exception\ValidateException;

class Model implements \IteratorAggregate, \ArrayAccess, \Serializable, \Countable
{
    protected array $_rules    = [];
    protected array $_messages = [];
    protected array $_alias    = [];

    public function __construct(array $data = [])
    {
        $this->unmarshall($data);
    }

    /**
     * @throws \Exception
     */
    public function __toString(): string
    {
        return $this->serialize();
    }

    public function unmarshall(array $data = []): void
    {
        $properties = $this->properties();
        foreach ($data as $key => $val) {
            if (\in_array($key, $properties)) {
                if ($this->{$key} instanceof self) {
                    $this->{$key}->unmarshall($val);
                } else {
                    $this->{$key} = $val;
                }
            }
        }
    }

    public function properties(): array
    {
        $vars = get_object_vars($this);
        unset($vars['_rules'], $vars['_messages'], $vars['_alias']);

        return array_keys($vars);
    }

    public function validate(bool $exception = false): Validation
    {
        Container::bindNX('validator', Validator::class);
        $validator  = Container::validator();
        $validation = $validator->make(get_object_vars($this), $this->_rules);
        $validation->setAliases($this->_alias);
        $validation->setMessages($this->_messages);
        $validation->validate();
        if ($validation->fails() && true === $exception) {
            $error     = $validation->errors()->firstOfAll();
            $prop_name = array_key_first($error);
            $msg       = $error[$prop_name];

            throw new ValidateException($prop_name, $msg);
        }
        unset($validator, $properties);

        return $validation;
    }

    public function toJson($options = 0, $depth = 512): string
    {
        return json_encode($this, $options, $depth);
    }

    public function offsetExists($offset): bool
    {
        return null !== $this->{$offset};
    }

    public function offsetGet($offset): mixed
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value): void
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset): void
    {
        $this->{$offset} = null;
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator(
            $this,
            \ArrayIterator::ARRAY_AS_PROPS | \ArrayIterator::STD_PROP_LIST
        );
    }

    public function count(): int
    {
        return \count($this->properties());
    }

    public function toArray(): array
    {
        return json_decode($this->toJson(), true);
    }

    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    public function unserialize($data): self
    {
        $data  = unserialize((string) $data);
        $class = static::class;
        $model = new $class($data);

        return $this->inherit($model, $model->properties());
    }

    public function inherit(self $from_model, $properties = []): self
    {
        foreach ($properties as $prop) {
            $this->{$prop} = $from_model->{$prop};
        }

        return $this;
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $class = static::class;
        $model = new $class($data);

        $this->inherit($model, $model->properties());
    }
}

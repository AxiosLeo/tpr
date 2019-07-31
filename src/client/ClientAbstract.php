<?php

declare(strict_types=1);

namespace tpr\client;

use tpr\exception\OptionSetErrorException;

/**
 * Class ClientAbstract.
 *
 * @method string name()
 * @method bool   debug()
 * @method string namespace()
 */
abstract class ClientAbstract implements ClientInterFace
{
    protected $app_options = [];

    public function __call($name, $arguments)
    {
        unset($arguments);
        if (isset($this->app_options[$name])) {
            return $this->app_options[$name];
        }

        return null;
    }

    abstract public function run();

    /**
     * @param array|string $key
     * @param mixed        $value
     *
     * @return $this
     */
    public function setOption($key, $value = null)
    {
        if (\is_array($key)) {
            $this->app_options = array_merge($this->app_options, $key);
        } else {
            if (!isset($this->app_options[$key])) {
                throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Name);
            }

            if (\gettype($value) !== \gettype($this->app_options[$key])) {
                throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Value_Type);
            }

            $this->app_options[$key] = $value;
        }

        return $this;
    }

    public function options($key = null)
    {
        if (null === $key) {
            return $this->app_options;
        }
        if (!isset($this->app_options[$key])) {
            throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Name);
        }

        return $this->app_options[$key];
    }

    abstract protected function init();
}

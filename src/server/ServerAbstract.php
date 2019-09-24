<?php

declare(strict_types=1);

namespace tpr\server;

use tpr\exception\OptionSetErrorException;
use tpr\library\ArrayTool;

/**
 * Class ClientAbstract.
 *
 * @method string name()
 * @method bool   debug()
 * @method string namespace()
 */
abstract class ServerAbstract implements ServerInterFace
{
    /**
     * @var ServerNameEnum
     */
    protected $server_name;
    protected $app_options = [];

    /**
     * @var ArrayTool
     */
    protected $options;

    public function __call($name, $arguments)
    {
        unset($arguments);

        return $this->optionsProvider()->get($name);
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
        $this->optionsProvider()->set($key, $value);

        return $this;
    }

    public function options($key = null)
    {
        if (null === $key) {
            return $this->app_options;
        }
        $value = $this->optionsProvider()->get($key);
        if (null === $value) {
            throw new OptionSetErrorException($key, OptionSetErrorException::Not_Supported_Option_Name);
        }

        return $value;
    }

    public function getServerName()
    {
        return $this->server_name;
    }

    abstract protected function init();

    private function optionsProvider()
    {
        if (null === $this->options) {
            $this->options = new ArrayTool($this->app_options);
        }

        return $this->options;
    }
}

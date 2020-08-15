<?php

declare(strict_types=1);

namespace tpr\core;

use InvalidArgumentException;
use tpr\traits\FindDataFromArrayTrait;

class Env
{
    use FindDataFromArrayTrait;

    private $env_array = [];

    private $env_files = [];

    private $env_map = [];

    public function __construct()
    {
        $this->addEnvFile(\tpr\Path::root() . '.env');
    }

    public function addEnvFile($path): self
    {
        if (file_exists($path) && !\in_array($path, $this->env_files)) {
            $result          = parse_ini_file($path, true);
            $this->env_array = $this->env_array === [] ? $result : array_merge($this->env_array, $result);
            array_push($this->env_files, $path);
        }

        return $this;
    }

    public function reload(): self
    {
        $this->env_array = [];
        $this->env_map   = [];
        foreach ($this->env_files as $env_file) {
            $this->addEnvFile($env_file);
        }

        return $this;
    }

    public function all()
    {
        return $this->env_array;
    }

    public function get($key, $default = null)
    {
        $env = $this->getFromEnvMap($key);
        if (null !== $env) {
            return $env;
        }
        $this->env_map[$key] = $this->find(explode('.', $key), $this->env_array, $default);

        return $this->env_map[$key];
    }

    public function getFromSys($key, $default = null)
    {
        $env = getenv($key);

        return null === $env ? $default : $env;
    }

    public function set($key, $value): self
    {
        $this->env_map[$key] = $value;

        return $this;
    }

    private function getFromEnvMap($key)
    {
        if (null === $key) {
            throw new InvalidArgumentException('Env key cannot be null.');
        }
        if (isset($this->env_map[$key])) {
            return $this->env_map[$key];
        }

        return null;
    }
}

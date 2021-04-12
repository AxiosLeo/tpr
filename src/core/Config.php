<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\App;
use function tpr\functions\fs\search;
use tpr\library\ArrayMap;
use tpr\Path;
use tpr\traits\CacheTrait;

final class Config
{
    use CacheTrait;

    public ArrayMap $config;

    public function __construct()
    {
        self::$cache_key = 'cache.config';
        if (App::debugMode()) {
            $this->load();
        } else {
            $config = $this->cache();
            if (null === $config) {
                $this->load();
            } else {
                $this->config = new ArrayMap($config);
            }
        }
    }

    public function set($name, $value): self
    {
        $this->config->set($name, $value);

        return $this;
    }

    public function load(string $path = null): self
    {
        $this->config = new ArrayMap();
        if (null === $path) {
            $path = Path::config();
        }
        $config_file_list = search($path, ['yaml', 'yml', 'json', 'ini', 'php', 'xml']);
        foreach ($config_file_list as $filepath) {
            $this->loadFile($filepath);
        }
        if (!App::debugMode()) {
            $this->cache($this->config->get());
        }

        return $this;
    }

    public function loadFile($file_path)
    {
        $ext    = pathinfo($file_path, \PATHINFO_EXTENSION);
        $group  = str_replace([Path::config() . \DIRECTORY_SEPARATOR, '.' . $ext], '', $file_path);
        $prefix = implode('.', explode(\DIRECTORY_SEPARATOR, $group));
        $this->config->set($prefix, array_merge(\Noodlehaus\Config::load($file_path)->all(), $this->config->get($prefix, [])));
    }

    /**
     * @param mixed $default
     *
     * @return null|array|mixed
     */
    public function get(string $name = null, $default = null)
    {
        return $this->config->get($name, $default);
    }
}

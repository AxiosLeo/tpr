<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\App;
use tpr\library\ArrayMap;
use tpr\Path;
use tpr\traits\CacheTrait;

class Config
{
    use CacheTrait;

    public ArrayMap $config;

    private string $cache_file;

    public function __construct()
    {
        $this->cache_file = Path::join(Path::cache(), 'config.cache');
        if (App::debugMode()) {
            $this->load();
        } else {
            $config = $this->cache($this->cache_file);
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
        $config_file_list = \tpr\Files::search($path, ['yaml', 'yml', 'json', 'ini', 'php', 'xml']);
        foreach ($config_file_list as $filepath) {
            $ext    = pathinfo($filepath, PATHINFO_EXTENSION);
            $group  = str_replace([Path::config() . \DIRECTORY_SEPARATOR, '.' . $ext], '', $filepath);
            $prefix = implode('.', explode(\DIRECTORY_SEPARATOR, $group));
            $this->config->set($prefix, array_merge(\Noodlehaus\Config::load($filepath)->all(), $this->config->get($prefix, [])));
        }
        $this->cache($this->cache_file, $this->config->get());

        return $this;
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

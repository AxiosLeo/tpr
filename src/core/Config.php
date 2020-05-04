<?php

declare(strict_types=1);

namespace tpr\core;

use Noodlehaus\Config as NoodlehausConfig;
use tpr\App;
use tpr\library\traits\FindDataFromArray;
use tpr\traits\CacheTrait;

class Config
{
    use FindDataFromArray;
    use CacheTrait;

    public $config = [];

    private $config_path;
    private $cache_file;

    public function __construct()
    {
        $this->cache_file  = \tpr\Path::cache() . \DIRECTORY_SEPARATOR . 'config.cache';
        $this->config_path = \tpr\Path::config();
        $this->init();
    }

    public function init()
    {
        if (!App::debugMode()) {
            $config = $this->cache($this->cache_file);
            if (false === $config) {
                $this->load();
            } else {
                $this->config = $config;
            }
        } else {
            $this->load();
        }
    }

    /**
     * @param null|string $path
     *
     * @return array
     */
    public function load($path = null)
    {
        if (null === $path) {
            $path = $this->config_path;
        } else {
            $path = \tpr\Path::format($path);
        }
        $config_file_list = \tpr\Files::searchAllFiles($path, ['yaml', 'yml', 'json', 'ini', 'php', 'xml']);
        foreach ($config_file_list as $file_path => $filename) {
            $group = str_replace(\tpr\Path::config(), '', $file_path);

            if (false === strpos($group, '/')) {
                $group = $filename;
            } else {
                $group = substr($group, 0, strpos($group, '/'));
            }

            $config = NoodlehausConfig::load($file_path)->all();
            if (!empty($config)) {
                if (isset($this->config[$group]) && !empty($this->config[$group])) {
                    $this->config[$group] = array_merge($this->config[$group], $config);
                } else {
                    $this->config[$group] = $config;
                }
            }
        }
        $this->cache($this->cache_file, $this->config);

        return $this->config;
    }

    /**
     * @param null|string $name
     * @param mixed       $default
     *
     * @return null|array|mixed
     */
    public function get($name = null, $default = null)
    {
        if (null === $name) {
            return $this->config;
        }
        $config = $this->find(explode('.', $name), $this->config, $default);
        if (!empty($default) && \is_array($default)) {
            $config = array_merge($default, $config);
        }

        return $config;
    }
}

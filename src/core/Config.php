<?php

declare(strict_types=1);

namespace tpr\core;

use Noodlehaus\Config as NoodlehausConfig;
use tpr\App;
use tpr\Cache;
use tpr\library\traits\FindDataFromArray;

class Config
{
    use FindDataFromArray;

    public $config_path = '';

    public $config = [];

    public function __construct()
    {
        $this->config_path = \tpr\Path::config();
        $this->init();
    }

    public function init()
    {
        if (!App::client()->debug()) {
            $config = $this->cache();
            if (false === $config) {
                $this->load();
            } else {
                $this->config = $config;
            }
        } else {
            $this->load();
        }
    }

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
        $this->cache($this->config);

        return $this->config;
    }

    public function get($name = null, $default = null)
    {
        if (null === $name) {
            return $this->config;
        }
        $config = $this->find(explode('.', $name), $this->config, $default);
        if (!empty($default) && is_array($default)) {
            $config = array_merge($default, $config);
        }

        return $config;
    }

    private function cache($data = null)
    {
        $config_cache_key = 'tpr_config';
        if (null === $data) {
            if (true === App::client()->debug() || !Cache::contains($config_cache_key)) {
                return false;
            }

            return Cache::fetch($config_cache_key);
        }
        if (!App::client()->debug()) {
            Cache::save($config_cache_key, $data, App::client()->options('cache_time'));
        }
        unset($config_cache_key);

        return $data;
    }
}

<?php

namespace tpr\core;

use Noodlehaus\Config as NoodlehausConfig;
use tpr\Cache;

class Config
{
    public $config_path = '';

    public $config = [];

    public function __construct()
    {
        $this->config_path = \tpr\Path::config();
        $this->init();
    }

    public function init()
    {
        if (!\tpr\App::debug()) {
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
        if (!empty($default) && \is_array($default)) {
            $config = array_merge($default, $config);
        }

        return $config;
    }

    private function cache($data = null)
    {
        $config_cache_key = 'tpr_config';
        if (null === $data) {
            if (true === \tpr\App::debug() || !Cache::contains($config_cache_key)) {
                return false;
            }

            return Cache::fetch($config_cache_key);
        }
        if (!\tpr\App::debug()) {
            Cache::save($config_cache_key, $data, \tpr\App::options('cache_time'));
        }
        unset($config_cache_key);

        return $data;
    }

    /**
     * @param $keyArray
     * @param $array
     * @param $default
     *
     * @return mixed
     */
    private function find($keyArray, $array, $default = null)
    {
        if (1 === \count($keyArray)) {
            return isset($array[$keyArray[0]]) ? $array[$keyArray[0]] : $default;
        }
        $key0 = $keyArray[0];
        unset($keyArray[0]);
        $keyArray = array_values($keyArray);
        if (!isset($array[$key0])) {
            return $default;
        }

        return $this->find($keyArray, $array[$key0], $default);
    }
}

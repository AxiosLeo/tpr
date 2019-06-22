<?php

namespace tpr\core;

use Noodlehaus\Config as NoodlehausConfig;
use tpr\lib\ArrayTool;
use tpr\Files;
use tpr\Path;

final class Config
{
    public $config_path = "";

    public $config = [];

    public function __construct()
    {
        $this->config_path = Path::config();
    }

    public function init()
    {
        if (!\tpr\App::debug()) {
            $config = $this->cache();
            if (false === $config) {
                $this->load();
            } else {
                $this->config = ArrayTool::instance($config);
            }
        } else {
            $this->load();
        }
    }

    private function cache($data = null)
    {
        $Cache            = new Cache();
        $config_cache_key = "tpr_config";
        if (is_null($data)) {
            if (\tpr\App::debug() === true || !$Cache->has($config_cache_key)) {
                return false;
            }
            return $Cache->get($config_cache_key);
        }
        if (!\tpr\App::debug()) {
            $Cache->set($config_cache_key, $data);
        }
        unset($Cache);
        unset($config_cache_key);
        return $data;
    }

    public function load($path = null)
    {
        if (is_null($path)) {
            $path = $this->config_path;
        } else {
            $path = Path::format($path);
        }
        $config_file_list = Files::searchAllFiles($path, ["yaml", "yml", "json", "ini", "php", "xml"]);

        foreach ($config_file_list as $file_path => $filename) {
            $config = NoodlehausConfig::load($file_path)->all();
            if (!empty($config)) {
                $this->config[$filename] = $config;
            }
        }
        $this->cache($this->config);
        return $this->config;
    }

    public function get($name = null, $default = null)
    {
        return $this->find(explode('.', $name), $this->config, $default);
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
        if (1 === count($keyArray)) {
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
<?php

namespace tpr\core;

class Path
{
    const DS = \DIRECTORY_SEPARATOR;

    private $path = [
        'framework' => TPR_FRAMEWORK_PATH,
        'root'      => '',
        'app'       => '',
        'command'   => '',
        'config'    => '',
        'runtime'   => '',
        'cache'     => '',
        'vendor'    => '',
        'index'     => '',
        'views'     => '',
        'lang'      => '',
    ];

    private $default_path = [
        'app'     => 'application',
        'config'  => 'config',
        'runtime' => 'runtime',
        'vendor'  => 'vendor',
        'index'   => 'public',
        'views'   => 'views',
        'command' => 'command',
        'lang'    => 'lang',
    ];

    public function __call($name, $arguments)
    {
        if (empty($arguments)) {
            return $this->get($name);
        }
        if (empty($arguments[0])) {
            return $this->get($name);
        }

        return $this->set($name, $arguments[0]);
    }

    public function check(): array
    {
        if (empty($this->path['root'])) {
            $this->path['root'] = \dirname(\dirname(\dirname(TPR_FRAMEWORK_PATH))) . self::DS;
        }
        foreach ($this->default_path as $key => $value) {
            if (empty($this->path[$key])) {
                $this->path[$key] = $this->path['root'] . $value . self::DS;
            }
        }
        if (empty($this->path['cache'])) {
            $this->path['cache'] = $this->path['runtime'] . \tpr\App::name() . self::DS;
        }

        return $this->all();
    }

    public function all(): array
    {
        return $this->path;
    }

    public function format($path, $create = false): string
    {
        $path = \DIRECTORY_SEPARATOR != substr($path, -1) ? $path . \DIRECTORY_SEPARATOR : $path;
        if ($create && !file_exists($path)) {
            if (!mkdir($path, 0700, true)) {
                return null;
            }
        }

        return $path;
    }

    public function dir($arrayDirItem, $divider = \DIRECTORY_SEPARATOR): string
    {
        $path = '';
        foreach ($arrayDirItem as $item) {
            $path .= $item . $divider;
        }

        return $path;
    }

    private function get($path_name): string
    {
        return isset($this->path[$path_name]) ? $this->path[$path_name] : '';
    }

    private function set($path_name, $path): string
    {
        $this->path[$path_name] = $path;

        return $this->path[$path_name];
    }
}

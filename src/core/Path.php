<?php

declare(strict_types=1);

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

    /**
     * @throws \Exception
     *
     * @return array
     */
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
            $this->path['cache'] = $this->path['runtime'] . 'cache' . self::DS;
        }

        return $this->all();
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @param bool   $create
     *
     * @return string
     */
    public function format($path, $create = false): string
    {
        $path = \DIRECTORY_SEPARATOR != substr($path, -1) ? $path . \DIRECTORY_SEPARATOR : $path;
        if ($create && !file_exists($path)) {
            @mkdir($path, 0700, true);
        }

        return $path;
    }

    /**
     * @param array $paths
     *
     * @return string
     */
    public function dir($paths): string
    {
        return implode(\DIRECTORY_SEPARATOR, $paths) . \DIRECTORY_SEPARATOR;
    }

    /**
     * @param string ...$paths
     *
     * @return string
     */
    public function join(...$paths)
    {
        if (0 === \count($paths)) {
            throw new \InvalidArgumentException('At least one parameter needs to be passed in.');
        }
        $pathResult = null;
        foreach ($paths as $i => $path) {
            if (null === $pathResult) {
                $pathResult = explode('/', $path);

                continue;
            }
            $tmp = explode('/', $path);
            foreach ($tmp as $str) {
                if ('..' === $str) {
                    array_pop($pathResult);
                } elseif ('.' === $str) {
                    continue;
                } else {
                    array_push($pathResult, $str);
                }
            }
        }

        return implode(\DIRECTORY_SEPARATOR, $pathResult);
    }

    /**
     * @param $path_name
     *
     * @return string
     */
    public function get($path_name): string
    {
        return isset($this->path[$path_name]) ? $this->path[$path_name] : '';
    }

    /**
     * @param $path_name
     * @param $path
     *
     * @return string
     */
    public function set($path_name, $path): string
    {
        $this->path[$path_name] = $this->format($path);

        return $this->path[$path_name];
    }
}

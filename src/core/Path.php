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

    public function __construct()
    {
        if (empty($this->path['root'])) {
            $this->path['root'] = \dirname(\dirname(\dirname(TPR_FRAMEWORK_PATH))) . self::DS;
        }
        foreach ($this->default_path as $key => $value) {
            if ('' === $this->path[$key]) {
                if ('' !== \tpr\Path::$subPath) {
                    $this->path[$key] = $this->path['root'] . $value . self::DS . \tpr\Path::$subPath . self::DS;
                } else {
                    $this->path[$key] = $this->path['root'] . $value . self::DS;
                }
            }
        }
        if ('' === $this->path['cache']) {
            $this->path['cache'] = $this->path['runtime'] . 'cache' . self::DS;
        }
    }

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
        $path = self::DS != substr($path, -1) ? $path . self::DS : $path;
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
        return implode(self::DS, $paths) . self::DS;
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
        $pathResult = explode(self::DS, $paths[0]);
        unset($paths[0]);
        $pathResultLen = \count($pathResult);
        if ('' === $pathResult[$pathResultLen - 1]) {
            unset($pathResult[$pathResultLen - 1]);
        }
        foreach ($paths as $path) {
            $tmp = explode(self::DS, $path);
            foreach ($tmp as $str) {
                if ('..' === $str) {
                    array_pop($pathResult);
                } elseif ('.' === $str || '' === $str) {
                    continue;
                } else {
                    array_push($pathResult, $str);
                }
            }
        }

        return implode(self::DS, $pathResult);
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

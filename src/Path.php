<?php

declare(strict_types=1);

namespace tpr;

use tpr\models\AppPathModel;

/**
 * Class Path.
 *
 * @method string framework() static
 * @method string root()      static
 * @method string app()       static
 * @method string command()   static
 * @method string config()    static
 * @method string runtime()   static
 * @method string cache()     static
 * @method string vendor()    static
 * @method string index()     static
 * @method string views()     static
 * @method string langs()     static
 */
class Path
{
    private static ?AppPathModel $model = null;

    private static array $cache = [];

    public static function __callStatic(string $name, $arguments)
    {
        unset($arguments);
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        self::$cache[$name] = self::configurate()->{$name} ?
            self::join(self::configurate()->root, self::configurate()->{$name}) : self::configurate()->root;

        return self::$cache[$name];
    }

    public static function configurate(array $config = []): AppPathModel
    {
        if (null === self::$model) {
            self::$model = new AppPathModel();
        }
        self::$model->unmarshall($config);
        self::$cache['root']      = self::$model->root;
        self::$cache['framework'] = self::$model->framework;

        return self::$model;
    }

    public static function join(string ...$paths): string
    {
        $is_win = PHP_SHLIB_SUFFIX === 'dll';
        if (0 === \count($paths)) {
            throw new \InvalidArgumentException('At least one parameter needs to be passed in.');
        }
        $base          = array_shift($paths);
        if ($is_win && false !== strpos($base, \DIRECTORY_SEPARATOR)) {
            $pathResult    = explode(\DIRECTORY_SEPARATOR, $base);
        } else {
            $pathResult    = explode('/', $base);
        }

        $pathResultLen = \count($pathResult);
        if ('' === $pathResult[$pathResultLen - 1]) {
            unset($pathResult[$pathResultLen - 1]);
        }
        foreach ($paths as $path) {
            $tmp = explode('/', $path);
            foreach ($tmp as $str) {
                if ('..' === $str) {
                    array_pop($pathResult);
                } elseif ('.' === $str || '' === $str) {
                    continue;
                } else {
                    $pathResult[] = $str;
                }
            }
        }

        return implode(\DIRECTORY_SEPARATOR, $pathResult);
    }
}

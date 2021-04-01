<?php

declare(strict_types=1);

namespace tpr\traits;

use tpr\App;
use tpr\Files;
use tpr\Path;

trait CacheTrait
{
    private static array $cache_data = [];

    private static string $cache_key = '';

    public static function clear(): void
    {
        self::$cache_data = [];
        $cache_time       = App::drive()->getConfig()->cache_time;
        $count            = 0 === $cache_time ? 'cache' : (int) (time() / $cache_time);
        $cache_file       = Path::join(Path::cache(), self::$cache_key, (string) $count . '.php');
        @unlink($cache_file);
    }

    private function cache(?array $data = null): ?array
    {
        if (true === App::debugMode()) {
            return null;
        }
        $cache_time = App::drive()->getConfig()->cache_time;
        $count      = 0 === $cache_time ? 'cache' : (int) (time() / $cache_time);
        $key        = self::$cache_key;
        $cache_file = Path::join(Path::cache(), $key, (string) $count . '.php');
        if (null === $data) {
            if (!isset(self::$cache_data[$key])) {
                if (!file_exists($cache_file)) {
                    Files::remove($key);

                    return null;
                }
                self::$cache_data[$key] = require_once $cache_file;

                return self::$cache_data[$key];
            }

            return self::$cache_data[$key];
        }
        if (!isset(self::$cache_data[$key])) {
            self::$cache_data[$key] = $data;
            Files::save($cache_file, '<?php' . \PHP_EOL . 'return ' . var_export($data, true) . ';' . \PHP_EOL);
        }

        unset($cache_file, $cache_time, $count);

        return $data;
    }
}

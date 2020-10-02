<?php

declare(strict_types=1);

namespace tpr\traits;

use tpr\App;
use tpr\Files;

trait CacheTrait
{
    private static array $cache_data = [];

    private function cache(string $tmp_file, ?array $tmp_data = null): ?array
    {
        if (true === App::debugMode()) {
            return null;
        }
        $cache_time = App::drive()->getConfig()->cache_time;
        $count      = 0 === $cache_time ? 'cache' : (int) (time() / $cache_time);
        $cache_file = $tmp_file . \DIRECTORY_SEPARATOR . (string) $count . '.php';
        if (null === $tmp_data) {
            if (!isset(self::$cache_data[$tmp_file])) {
                if (!file_exists($cache_file)) {
                    Files::remove($tmp_file);

                    return null;
                }
                self::$cache_data[$tmp_file] = require_once $cache_file;

                return self::$cache_data[$tmp_file];
            }

            return self::$cache_data[$tmp_file];
        }
        if (!isset(self::$cache_data[$tmp_file])) {
            self::$cache_data[$tmp_file] = $tmp_data;
            Files::save($cache_file, '<?php' . PHP_EOL . 'return ' . var_export($tmp_data, true) . ';' . PHP_EOL);
        }

        unset($cache_file, $cache_time, $count);

        return $tmp_data;
    }
}

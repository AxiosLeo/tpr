<?php

declare(strict_types=1);

namespace tpr\traits;

use tpr\App;
use tpr\Files;

trait CacheTrait
{
    private function cache(string $tmp_file, ?array $tmp_data = null): ?array
    {
        // count cache file name
        $cache_time = App::drive()->getConfig()->cache_time;
        $count      = (int) (time() / $cache_time);
        $cache_file = $tmp_file . \DIRECTORY_SEPARATOR . (string) $count . '.php';
        if (null === $tmp_data) {
            if (true === App::debugMode()) {
                return null;
            }
            if (!file_exists($cache_file)) {
                Files::remove($tmp_file);

                return null;
            }

            return require_once $cache_file;
        }

        if (!App::debugMode()) {
            Files::save($cache_file, '<?php' . PHP_EOL . 'return ' . var_export($tmp_data, true) . ';' . PHP_EOL);
        } else {
            Files::remove($cache_file);
        }
        unset($cache_file, $cache_time, $count);

        return $tmp_data;
    }
}

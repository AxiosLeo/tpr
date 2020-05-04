<?php

declare(strict_types=1);

namespace tpr\traits;

use tpr\App;

trait CacheTrait
{
    /**
     * @param string $tmp_file
     * @param null   $tmp_data
     *
     * @return null|mixed
     */
    private function cache($tmp_file, $tmp_data = null)
    {
        $tmp_file .= '.php';
        if (null === $tmp_data) {
            if (true === App::debugMode() || !\tpr\Files::exist($tmp_file)) {
                return null;
            }

            return require $tmp_file;
        }
        if (!App::debugMode()) {
            file_put_contents($tmp_file, "<?php\nreturn " . var_export($tmp_data, true) . ";\n");
        }
        unset($tmp_file);

        return $tmp_data;
    }
}

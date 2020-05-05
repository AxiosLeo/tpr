<?php

declare(strict_types=1);

namespace tpr\traits;

use tpr\App;
use tpr\Files;

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
            if (true === App::debugMode() || !Files::exist($tmp_file)) {
                return null;
            }

            return require $tmp_file;
        }
        if (!App::debugMode()) {
            Files::save($tmp_file, "<?php\nreturn " . var_export($tmp_data, true) . ";\n");
        } else {
            Files::delete($tmp_file);
        }
        unset($tmp_file);

        return $tmp_data;
    }
}

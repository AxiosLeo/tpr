<?php

namespace tpr;

use tpr\core\Files as CoreFiles;

/**
 * Class Files.
 *
 * @see     CoreFiles
 *
 * @method readJsonFile($filename, $is_array = true)                                             static
 * @method searchFile($dir, $extArray = [], $exclude = [])                                       static
 * @method searchDir($dir, $exclude = [])                                                        static
 * @method save($filename, $text, $blank = 0)                                                    static
 * @method append($filename, $text, $blank = 0)                                                  static
 * @method searchAllFiles($dir, $extInclude = "*", $asc = false, $sorting_type = SORT_FLAG_CASE) static
 * @method delete($path)                                                                         static
 */
class Files extends Facade
{
    protected static function getContainName()
    {
        return 'files';
    }

    protected static function getFacadeClass()
    {
        return CoreFiles::class;
    }
}

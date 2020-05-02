<?php

declare(strict_types=1);

namespace tpr;

use tpr\core\Files as CoreFiles;

/**
 * Class Files.
 *
 * @see     CoreFiles
 *
 * @method array  readJsonFile($filename)                                                               static
 * @method array  searchFile($dir, $extArray = [], $exclude = [])                                       static
 * @method array  searchDir($dir, $exclude = [])                                                        static
 * @method array  searchAllFiles($dir, $extInclude = "*", $asc = false, $sorting_type = SORT_FLAG_CASE) static
 * @method void   save($filename, $text, $blank = 0)                                                    static
 * @method void   append($filename, $text, $blank = 0)                                                  static
 * @method void   delete($path)                                                                         static
 * @method bool   move($source, $target)                                                                static
 * @method bool   copy($source, $target)                                                                static
 * @method string read($path, $offset = 0, $maxlen = null)                                              static
 * @method bool   exist($path)                                                                          static
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

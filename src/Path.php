<?php

namespace tpr;

use tpr\core\Path as CorePath;

/**
 * Class Path.
 *
 * @see     CorePath
 *
 * @method array  check()                                            static
 * @method array  all()                                              static
 * @method string root($path = null)                                 static
 * @method string app($path = null)                                  static
 * @method string command($path = null)                              static
 * @method string config($path = null)                               static
 * @method string runtime($path = null)                              static
 * @method string vendor($path = null)                               static
 * @method string framework($path = null)                            static
 * @method string index($path = null)                                static
 * @method string views($path = null)                                static
 * @method string cache($path = null)                                static
 * @method string format($path, $create = false)                     static
 * @method string dir($arrayDirItem, $divider = DIRECTORY_SEPARATOR) static
 */
class Path extends Facade
{
    protected static function getContainName()
    {
        return 'path';
    }

    protected static function getFacadeClass()
    {
        return CorePath::class;
    }
}

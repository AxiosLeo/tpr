<?php

namespace tpr;

use tpr\core\Config as CoreConfig;

/**
 * Class Config.
 *
 * @see     CoreConfig
 *
 * @method mixed get($name = null, $default = null) static
 * @method void  load($path = null)                 static
 */
final class Config extends Facade
{
    protected static function getContainName()
    {
        return 'config';
    }

    protected static function getFacadeClass()
    {
        return CoreConfig::class;
    }
}

<?php

declare(strict_types=1);

namespace tpr;

use tpr\core\Config as CoreConfig;

/**
 * Class Config.
 *
 * @see     CoreConfig
 *
 * @method mixed      get($name = null, $default = null) static
 * @method CoreConfig set($name, $value)                 static
 * @method void       load($path = null)                 static
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

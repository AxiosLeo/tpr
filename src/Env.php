<?php

declare(strict_types=1);

namespace tpr;

use tpr\core\Env as CoreEnv;

/**
 * Class Env.
 *
 * @see CoreEnv
 *
 * @method CoreEnv addEnvFile($path)                 static
 * @method CoreEnv reload()                          static
 * @method mixed   get($key, $default = null)        static
 * @method mixed   getFromSys($key, $default = null) static
 * @method array   all()                             static
 * @method CoreEnv set($key, $value)                 static
 */
class Env extends Facade
{
    protected static function getContainName()
    {
        return 'env';
    }

    protected static function getFacadeClass()
    {
        return CoreEnv::class;
    }
}

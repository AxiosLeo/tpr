<?php

declare(strict_types=1);

namespace tpr;

use tpr\core\Env as CoreEnv;

/**
 * Class Env.
 *
 * @see CoreEnv
 *
 * @method CoreEnv load($relative_path)              static
 * @method mixed   get($key = null, $default = null) static
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

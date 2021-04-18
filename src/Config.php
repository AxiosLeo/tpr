<?php

declare(strict_types=1);

namespace tpr;

/**
 * Class Config.
 *
 * @method mixed       get($name = null, $default = null) static
 * @method core\Config set($name, $value)                 static
 * @method void        load($path = null)                 static
 * @method void        loadFile($file_path)               static
 */
final class Config extends Facade
{
    protected static function getContainName(): string
    {
        return 'config';
    }

    protected static function getFacadeClass(): string
    {
        return core\Config::class;
    }
}

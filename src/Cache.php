<?php

namespace tpr;

use tpr\lib\Facade;
use tpr\core\Cache as CoreCache;

/**
 * Class Cache
 *
 * @package tpr
 * @method void set($key, $data, $timeout = 0) static
 * @method mixed get($key) static
 * @method bool has($key) static
 * @method bool rm($key) static
 * @method false|int inc($key, $step = 1) static
 * @method false|int dec($key, $step = 1) static
 * @method bool clear($tag = null) static
 * @method mixed pull($key) static
 * @method mixed remember($key, $value, $expire = null) static
 * @method $this tag($key, $keys = null, $overlay = false) static
 */
class Cache extends Facade
{
    protected static function getContainName()
    {
        return "cache";
    }

    protected static function getFacadeClass()
    {
        return CoreCache::class;
    }
}
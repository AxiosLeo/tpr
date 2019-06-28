<?php

namespace tpr;

use Doctrine\Common\Cache\ArrayCache;

/**
 * Class Cache.
 *
 * @method mixed      fetch($id)                      static
 * @method bool       contains($id)                   static
 * @method bool       save($id, $data, $lifeTime = 0) static
 * @method bool       delete($id)                     static
 * @method array|null getStats()
 */
class Cache extends Facade
{
    protected static function getContainName()
    {
        return 'cache';
    }

    protected static function getFacadeClass()
    {
        return ArrayCache::class;
    }
}

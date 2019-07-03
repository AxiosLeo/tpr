<?php

namespace tpr;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;

/**
 * Class Cache.
 *
 * @see ArrayCache
 *
 * @method mixed      fetch($id)                      static
 * @method bool       contains($id)                   static
 * @method bool       save($id, $data, $lifeTime = 0) static
 * @method bool       delete($id)                     static
 * @method null|array getStats()
 */
class Cache extends Facade
{
    protected static function getContainName()
    {
        return 'cache';
    }

    protected static function getFacadeClass()
    {
        return new FilesystemCache(Path::cache());
    }
}

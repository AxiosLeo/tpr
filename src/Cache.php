<?php

declare(strict_types=1);

namespace tpr;

use Psr\Cache\CacheItemInterface;
use think\cache\Driver;
use think\CacheManager;

/**
 * Class Cache.
 *
 * @see ArrayCache
 *
 * @method void                              init(array $config = [])                      static
 * @method Driver                            store(string $name = '', bool $force = false) static
 * @method Driver                            connect(array $options, string $name = '')    static
 * @method void                              config(array $config)                         static
 * @method CacheItemInterface                getItem($key)                                 static
 * @method CacheItemInterface[]|\Traversable getItems(array $keys = [])                    static
 * @method bool                              hasItem(string $key)                          static
 * @method bool                              clear()                                       static
 * @method bool                              deleteItem(string $key)                       static
 * @method bool                              deleteItems(array $keys)                      static
 * @method bool                              save(CacheItemInterface $item)                static
 * @method bool                              saveDeferred(CacheItemInterface $item)        static
 * @method bool                              commit()                                      static
 */
class Cache extends Facade
{
    protected static function getContainName()
    {
        return 'cache';
    }

    protected static function getFacadeClass()
    {
        return new CacheManager();
    }
}

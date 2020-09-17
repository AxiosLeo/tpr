<?php

declare(strict_types=1);

namespace tpr;

/**
 * Class Lang.
 *
 * @see  CoreLang
 *
 * @method void   load(string $lang_set_name, string $lang_file) static
 * @method string tran(string $word, $lang_set_name = null)      static
 */
class Lang extends Facade
{
    protected static function getContainName()
    {
        return 'lang';
    }

    protected static function getFacadeClass()
    {
        return core\Lang::class;
    }
}

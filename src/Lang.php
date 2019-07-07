<?php

namespace tpr;

use tpr\core\Lang as CoreLang;

/**
 * Class Lang.
 *
 * @see  CoreLang
 *
 * @method void   load(string $langSet, array $word) static
 * @method string tran(string $str, $langSet = null) static
 */
class Lang extends Facade
{
    protected static function getContainName()
    {
        return 'lang';
    }

    protected static function getFacadeClass()
    {
        return CoreLang::class;
    }
}

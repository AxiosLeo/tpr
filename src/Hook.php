<?php

namespace tpr;

use tpr\lib\Facade;
use tpr\core\Hook as CoreHook;

/**
 * Class Hook
 *
 * @package tpr
 * @see     CoreHook
 * @mixin CoreHook
 * @method void import(array $behaviors) static
 * @method void add(string $behavior_name, $behavior_class, string $behavior_method = "run", $params = [], $first = false) static
 * @method void listen(string $behavior_name, &$data = [], \Closure $callback = null) static
 * @method void listenFirst(string $behavior_name, &$data, \Closure $callback = null)static
 * @method array get(string $behavior_name = null) static
 * @method bool remove(string $behavior_name, int $index) static
 * @method bool delete(string $behavior_name)
 */
class Hook extends Facade
{
    protected static function getContainName()
    {
        return "hook";
    }

    protected static function getFacadeClass()
    {
        return CoreHook::class;
    }
}
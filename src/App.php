<?php

namespace tpr;

use tpr\core\App as CoreApp;

/**
 * Class App.
 *
 * @see     CoreApp
 *
 * @method CoreApp setAppOption($key, $value)   static
 * @method mixed   options($key)                static
 * @method void    run($debug = true)           static
 * @method CoreApp app()                        static
 * @method void    removeHeaders($headers = []) static
 * @method string  name()                       static
 * @method bool    debug()                      static
 * @method string  mode()                       static
 * @method string  namespace()                  static
 */
class App extends Facade
{
    protected static function getContainName()
    {
        return 'app';
    }

    protected static function getFacadeClass()
    {
        return CoreApp::class;
    }
}

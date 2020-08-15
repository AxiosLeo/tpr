<?php

declare(strict_types=1);

namespace tpr\models;

use tpr\Model;

final class AppModel extends Model
{
    /**
     * app name.
     */
    public string $name = 'app';

    /**
     * debug mode.
     */
    public bool $debug = false;

    /**
     * app base namespace.
     */
    public string $namespace = 'App';

    /**
     * default language set.
     */
    public string $lang = 'zh-cn';

    public array $server_options = [];
}

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

    /**
     * @var int global cache time for config&route data
     */
    public int $cache_time = 30;

    /**
     * @var string default content-type on cgi mode
     */
    public string $default_content_type_cgi = 'html';

    /**
     * @var string default content-type on api request
     */
    public string $default_content_type_ajax = 'json';

    /**
     * @var string default content-type on cli mode
     */
    public string $default_content_type_cli = 'text';

    /**
     * remove some header before send response.
     *
     * @var array|string[]
     */
    public array $remove_headers = [];

    /**
     * true : forces use routing mode.
     */
    public bool $force_route = false;

    /**
     * for dispatch route.
     */
    public string $dispatch_rule = '{app_namespace}\\{module}\\controller\\{controller}';

    /**
     * for ServerHandler custom config.
     */
    public array $server_options = [];

    /**
     * response config, see detail on \tpr\models\ResponseModel.
     */
    public array $response_config = [];
}

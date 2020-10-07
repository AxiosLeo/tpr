<?php

declare(strict_types=1);

namespace tpr\models;

use tpr\Model;

final class AppModel extends Model
{
    /**
     * the name of application.
     */
    public string $name = 'app';

    /**
     * the base namespace of application.
     */
    public string $namespace = 'App';

    /**
     * default language set.
     */
    public string $lang = 'zh-cn';

    /**
     * @var int global cache time for config&route data
     */
    public int $cache_time = 60;

    /**
     * @var string default content-type on cgi mode
     */
    public string $default_content_type_cgi = 'html';

    /**
     * @var string default content-type on api request
     */
    public string $default_content_type_ajax = 'json';

    /**
     * @var string default output format of data on cli mode
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
     * controller namespace spelling rule.
     */
    public string $controller_rule = '{app_namespace}\\{module}\\controller\\{controller}';

    /**
     * the configuration of custom server handler.
     */
    public array $server_options = [];

    /**
     * the global configuration of response, see detail in \tpr\models\ResponseModel.
     */
    public array $response_config = [];
}

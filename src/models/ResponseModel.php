<?php

declare(strict_types=1);

namespace tpr\models;

use tpr\App;
use tpr\core\response\Html;
use tpr\core\response\Json;
use tpr\core\response\Jsonp;
use tpr\core\response\Text;
use tpr\core\response\Xml;
use tpr\Model;

class ResponseModel extends Model
{
    public static array $allow_type = [
        'html'  => Html::class,
        'json'  => Json::class,
        'jsonp' => Jsonp::class,
        'text'  => Text::class,
        'xml'   => Xml::class,
    ];

    // common
    public array  $headers         = [];
    public array  $header_name_set = [];
    public string $return_type     = 'html';

    // html
    public array  $params            = [];
    public string $views_path        = '';
    public string $template_file_ext = 'html';

    // json&jsonp
    public int    $json_options  = JSON_UNESCAPED_UNICODE;
    public string $jsonp_handler = 'jsonpReturn';

    // xml
    public string $root_node = 'data'; // <data></data>
    public array  $root_attr = [];     // <data attrs></data>
    public string $item_node = 'item'; // <data><item></item></data>
    public string $item_key  = 'id';   // <data><item id=""></item></data>
    public string $encoding  = 'utf-8';

    public function __construct(array $data = [])
    {
        $this->return_type = App::drive()->getConfig()->default_content_type_cgi;
        parent::__construct($data);
    }
}

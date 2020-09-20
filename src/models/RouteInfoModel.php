<?php

declare(strict_types=1);

namespace tpr\models;

use tpr\Model;

final class RouteInfoModel extends Model
{
    public string $pathinfo = '';
    public string $method   = '';
    public string $handler  = '';
    public string $intro    = '';
    public array  $params   = [];
}

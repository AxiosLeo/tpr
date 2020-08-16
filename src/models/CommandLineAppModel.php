<?php

declare(strict_types=1);

namespace tpr\models;

use tpr\Model;

class CommandLineAppModel extends Model
{
    public string $name      = 'Command Tools';
    public string $version   = '0.0.1';
    public string $namespace = '';
    public array  $commands  = [];
}

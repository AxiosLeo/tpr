<?php

declare(strict_types=1);

namespace tpr\tests\mock;

use tpr\Model;

class SubModel extends Model
{
    public string $foo = '';
    public int    $val = 0;
}

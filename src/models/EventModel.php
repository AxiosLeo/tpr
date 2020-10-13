<?php

declare(strict_types=1);

namespace tpr\models;

use tpr\Model;

class EventModel extends Model
{
    public bool $init = false;

    public array $events = [];

    public ?string $password = null;

    public ?string $key = null;

    public bool $lock = false;
}

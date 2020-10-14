<?php

declare(strict_types=1);

use tpr\App;

require dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'autoload.php';

App::debugMode(true);
App::default();

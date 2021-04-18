<?php

declare(strict_types=1);

namespace tpr\models;

use tpr\Model;

final class AppPathModel extends Model
{
    public string $framework = TPR_FRAMEWORK_PATH;

    public string $root = '';

    public string $app = 'application';

    public string $command = 'commands';

    public string $config = 'config';

    public string $runtime = 'runtime';

    public string $cache = 'runtime/cache';

    public string $vendor = 'vendor';

    public string $index = 'public';

    public string $views = 'views';

    public string $langs = 'langs';

    public function __construct(array $data = [])
    {
        if (!isset($data['root'])) {
            $data['root'] = path_join($this->framework, '../../../');
        }
        parent::__construct($data);
    }
}

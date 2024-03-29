<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\Console;
use tpr\models\AppPathModel;
use tpr\Path;
use tpr\traits\CommandTrait;

class InitApp extends Console
{
    use CommandTrait;

    private string       $dir;
    private ?string      $app_name;
    private ?string      $namespace;
    private AppPathModel $path;
    private bool         $has_install = false;

    public function __construct(string $project_dir, string $app_name = null, string $base_namespace = null)
    {
        parent::__construct();
        $this->dir       = $project_dir;
        $this->app_name  = $app_name;
        $this->namespace = $base_namespace;
        $this->path      = new AppPathModel(['root' => $this->dir]);
    }

    public function start(?string $web_server = null)
    {
        $index_dir = Path::join($this->path->root, $this->path->index);
        if (null === $web_server) {
            $web_server = $this->selectServer();
        }

        switch ($web_server) {
            case 'php built-in web server':
                exec_command('php -S localhost:8088 -t ./', $index_dir);

                break;

            case 'workerman http web server':
                exec_command('php workerman.php start -d', $index_dir);

                break;
        }
    }

    public function selectServer(): string
    {
        $web_server = $this->output->choice('select web server', [
            'php built-in web server',
            'workerman http web server',
        ], 'php built-in web server');

        switch ($web_server) {
            case 'workerman http web server':
                $this->genWorkermanIndex();
        }
        $this->genIndex();

        return $web_server;
    }

    public function init(): bool
    {
        if (!file_exists($this->dir)) {
            $this->genAppFiles();
            $this->genComposer();

            $confirm_cli = $this->output->confirm('use cli command tool?', true);
            if ($confirm_cli) {
                $this->genCLIIndex();
            }
            $this->output->newLine(2);
        } else {
            $this->output->warning($this->dir . ' dir already exist.');
        }
        $web_server = $this->selectServer();
        $start      = $this->output->confirm('start web server right now', true);
        if ($start) {
            if (!$this->has_install) {
                exec_command('composer install', $this->dir);
            }
            $this->start($web_server);
        }

        return $start;
    }

    private function genAppFiles()
    {
        $tpr_version = TPR_FRAMEWORK_VERSION;
        $this->genFiles([
            // controller
            $this->path->app . '/index/controller/Index.php' => '<?php

namespace ' . $this->namespace . '\\index\\controller;

use tpr\\Controller;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}
',
            // generate library dir
            'library/README.md' => 'you can write code of utils in here.',
            // generate views dir
            'views/index/index/index.html' => '<!DOCTYPE html>
<html lang="en">
<head>
    <title>Welcome</title>
</head>
<body>
<!-- Document : https://twig.symfony.com/doc/2.x/   -->

<h1>TPR Framework Version ' . $tpr_version . '</h1>

</body>
</html>
',
            // generate routes configuration file
            'config/routes.php' => '<?php
// doc : https://github.com/AxiosCros/tpr/wiki/Route
// you can write routes data in here.
return [
];
',
            // generate events configuration file
            'config/events.php' => '<?php
// doc : https://github.com/AxiosCros/tpr/wiki/Event
// you can write events data in here.
return [
  // <event-name>::<class-name>::<function-name>
];
',
            // generate .php_cs.dist&.gitignore
            '.php_cs.dist' => file_get_contents(path_join(TPR_FRAMEWORK_PATH, '.php_cs.dist')),
            '.gitignore'   => file_get_contents(path_join(TPR_FRAMEWORK_PATH, '.gitignore')),
        ]);
    }

    /**
     * generate composer.json.
     */
    private function genComposer()
    {
        $app_path = $this->path->app;
        $content  = <<<EOF
{
  "require": {
    "axios/tpr": "~5.1.0"
  },
  "autoload": {
    "psr-4": {
      "library\\\\": "library/",
      "{$this->namespace}\\\\": "{$app_path}/"
    }
  },
  "repositories": {
    "packagist": {
      "type": "composer",
      "url": "https://mirrors.aliyun.com/composer/"
    }
  },
  "scripts": {
    "start": "echo 'http://localhost:8088' && php -S localhost:8088 -t public/"
  }
}
EOF;
        \axios\tools\Files::write(path_join($this->path->root, 'composer.json'), $content);
        if ($this->output->confirm('install composer libraries now?', true)) {
            exec_command('composer install', $this->dir);
            $this->has_install = true;
        }
    }

    /**
     * generate web entry file.
     */
    private function genIndex()
    {
        $gen_path = path_join($this->path->root, $this->path->index, 'index.php');
        if (file_exists($gen_path)) {
            return;
        }
        \axios\tools\Files::write(
            $gen_path,
            "<?php

namespace {$this->namespace}\\index;

use tpr\\App;

require_once __DIR__ . '/../vendor/autoload.php';

App::debugMode(true);

App::default()
    ->config([
        // doc : https://github.com/AxiosCros/tpr/wiki/Application
        'namespace'       => '{$this->namespace}',
        'lang'            => 'zh-cn',         // default language set name
        'cache_time'      => 60,              // global cache time for config&route data
        'force_route'     => false,           // forces use routing
        'remove_headers'  => [],              // remove some header before send response
        'server_options'  => [],              // for ServerHandler custom config.
        'response_config' => [],              // response config, see detail on \\tpr\\models\\ResponseModel.

        'default_content_type_cgi' => 'html', // default content-type on cgi mode
        'default_content_type_ajax'=> 'json', // default content-type on api request
        'default_content_type_cli' => 'text', // default content-type on command line mode

        'dispatch_rule'            => '{app_namespace}\\{module}\\controller\\{controller}',  // controller namespace spelling rule
    ])
    ->run();
"
        );
    }

    private function genWorkermanIndex()
    {
        $gen_path = path_join($this->path->root, $this->path->index, 'workerman.php');
        if (file_exists($gen_path)) {
            return;
        }
        $workerman_driver = '\\tpr\\server\\WorkermanServer::class';
        $content          = "<?php

namespace {$this->namespace}\\index;

use tpr\\App;

require_once __DIR__ . '/../vendor/autoload.php';

App::registerServer('workerman', {$workerman_driver});

App::drive('workerman')->config([
    // doc : https://github.com/AxiosCros/tpr-workerman
    'namespace'       => '{$this->namespace}',
    'server_options'  => [
        'port'     => 8088,
    ]
])->run();
";
        \axios\tools\Files::write($gen_path, $content);
        $this->requireLibrary('axios/tpr-workerman');
    }

    private function genCLIIndex()
    {
        $namespace = $this->namespace . '\\commands';
        $content   = <<<EOF
#!/usr/bin/env php
<?php

require_once __DIR__ . \\DIRECTORY_SEPARATOR . 'vendor'. \\DIRECTORY_SEPARATOR .'autoload.php';

use tpr\\Path;
use tpr\\App;

Path::configurate([
    'root'  => __DIR__ . \\DIRECTORY_SEPARATOR,
    'vendor'=> Path::join(Path::root(), 'vendor')
]);

App::default()->config([
    'namespace'      => '{$namespace}',
    'server_options' => [
        'commands' => [
            'make' => \\tpr\\command\\Make::class
        ]
    ]
])->run();
EOF;
        $this->genFiles([
            // generate commands dir
            'commands/README.md' => 'you can write code of commands in here.',
            'tpr'                => $content,
        ]);
        $this->requireLibrary('nette/php-generator', true);
        if ('WIN' !== substr(\PHP_OS, 0, 3)) {
            exec_command('chmod 755 ./tpr', $this->path->root);
        }
    }

    private function requireLibrary(string $package, bool $dev = false)
    {
        $cmd = 'cd ' . $this->dir . ' && composer require ' . $package;
        if ($dev) {
            $cmd .= ' --dev';
        }
        exec_command($cmd);
    }

    private function genFiles(array $files)
    {
        foreach ($files as $path => $content) {
            $p = path_join($this->path->root, $path);
            \axios\tools\Files::write($p, $content);
        }
    }
}

<?php

declare(strict_types=1);

namespace tpr\command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use tpr\Console;
use tpr\Files;
use tpr\models\AppPathModel;
use tpr\Path;
use tpr\traits\CommandTrait;

class Create extends Console
{
    use CommandTrait;

    private AppPathModel $path;

    protected function configure()
    {
        $this->setName('create')
            ->setDescription('create new tpr app')
            ->addArgument('app_name', InputArgument::REQUIRED)
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        unset($input, $output);
        $app_name = $this->input->getArgument('app_name');
        $dir      = (string) $this->input->getOption('output');
        if (empty($dir)) {
            $dir = Path::join(getcwd(), $app_name);
        }
        if (file_exists($dir)) {
            $this->output->error($app_name . ' already exist in ' . \dirname($dir));

            return;
        }
        $this->path = new AppPathModel(['root' => $dir]);
        $namespace  = $this->inputNamespace($app_name);
        $this->genController($namespace);

        // generate web entry file
        Files::save(
            Path::join($this->path->root, $this->path->index, 'index.php'),
            <<<EOF
<?php

namespace {$namespace}\\index;

use tpr\\App;

require_once __DIR__ . '/../vendor/autoload.php';

App::debugMode(true);

App::default()
    ->config(['namespace'=>"{$namespace}"])
    ->run();

EOF
        );

        // generate library dir
        Files::save(
            Path::join($this->path->root, 'library', 'README.md'),
            <<<'EOF'
you can write code of utils in here.
EOF
        );

        // generate views dir
        $tpr_version = TPR_FRAMEWORK_VERSION;
        Files::save(
            Path::join($this->path->root, 'views/index/index', 'index.html'),
            <<<EOF
<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
<!-- Document : https://twig.symfony.com/doc/2.x/   -->

<h1>TPR Framework Version {$tpr_version}</h1>

</body>
</html>
EOF
        );

        // generate config files
        Files::save(
            Path::join($this->path->root, $this->path->config, 'app.php'),
            <<<'EOF'
<?php

return [
    'default_return_type'      => 'html',
    'default_ajax_return_type' => 'json',
    'route_class_name'         => '{app_namespace}\{module}\controller\{controller}',
];

EOF
        );

        // generate composer.json
        $app_path = $this->path->app;
        Files::save(
            Path::join($this->path->root, 'composer.json'),
            <<<EOF
{
  "require": {
    "axios/tpr": "dev-master"
  },
  "autoload": {
    "psr-4": {
      "library\\\\": "library/",
      "{$namespace}\\\\": "{$app_path}/"
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

EOF
        );

        Files::save(
            Path::join($this->path->root, 'tpr'),
            <<<'EOF'
#!/usr/bin/env php
<?php

require_once __DIR__ . \DIRECTORY_SEPARATOR . 'vendor'. \DIRECTORY_SEPARATOR .'autoload.php';

use tpr\Path;
use tpr\App;

Path::configurate([
    'root'  => __DIR__ . \DIRECTORY_SEPARATOR,
    'vendor'=> Path::join(Path::root(), 'vendor')
]);

App::default()->config([
    'server_options' => [
        'commands' => [
            'make' => \tpr\command\Make::class
        ]
    ]
])->run();
EOF
        );
        $this->shell('chmod 755 ' . Path::join($this->path->root, 'tpr'));

        Files::save(
            Path::join($this->path->root, 'commands', 'README.md'),
            <<<'EOF'

EOF
        );

        // generate .php_cs.dist&.gitignore
        $files = [
            '.php_cs.dist',
            '.gitignore',
        ];
        foreach ($files as $file) {
            Files::save(
                Path::join($this->path->root, $file),
                file_get_contents(Path::join(TPR_FRAMEWORK_PATH, $file))
            );
        }

        $confirm = $this->output->confirm('composer install now?');
        if ($confirm) {
            $this->shell('cd ' . $dir . ' && composer install');
        }
        $this->output->newLine(2);
        $this->output->success('Created on ' . $dir);

        $confirm = $this->output->confirm('start web server right now', true);
        if ($confirm) {
            $this->shell('cd ' . $dir . ' && composer start');
        }
    }

    private function inputNamespace($app_name)
    {
        $namespace = $this->output->ask('input app namespace', $app_name);
        if ('\\' === $namespace[\strlen($namespace) - 1]) {
            $this->output->warning('Invalid namespace. namespace mustn\'t end with a namespace separator "\". ');

            return $this->inputNamespace($app_name);
        }

        return $namespace;
    }

    private function genController($namespace)
    {
        $gen_path = Path::join($this->path->root, $this->path->app, 'index', 'controller', 'Index.php');
        $content  = <<<EOF
<?php

namespace {$namespace}\\index\\controller;

use tpr\\Controller;

class Index extends Controller
{
    public function index()
    {
        return \$this->fetch();
    }
}

EOF;
        Files::save($gen_path, $content);
    }
}

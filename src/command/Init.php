<?php

declare(strict_types=1);

namespace tpr\command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use tpr\Console;
use tpr\core\InitApp;
use tpr\Path;
use tpr\traits\CommandTrait;

final class Init extends Console
{
    use CommandTrait;

    protected function configure()
    {
        $this->setName('init')
            ->setAliases(['create'])
            ->setDescription('Quickly initialize an application')
            ->addArgument('app_name', InputArgument::REQUIRED)
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        unset($input, $output);
        $app_name = $this->input->getArgument('app_name');
        $dir      = (string) $this->input->getOption('output');
        if (empty($dir)) {
            $dir = Path::join(getcwd(), $app_name);
        }
        $namespace = $this->inputNamespace($app_name);

        $init = new InitApp($dir, $app_name, $namespace);
        if (!$init->init()) {
            $this->output->success('Initialize application successful. Created on ' . $dir);
        }

        return 0;
    }

    private function inputNamespace(string $app_name)
    {
        $namespace = $this->output->ask('input app namespace', $app_name);
        if ('\\' === $namespace[\strlen($namespace) - 1]) {
            $this->output->warning('Invalid namespace. namespace mustn\'t end with a namespace separator "\". ');

            return $this->inputNamespace($app_name);
        }

        return $namespace;
    }
}

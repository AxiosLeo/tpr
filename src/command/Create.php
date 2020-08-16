<?php

declare(strict_types=1);

namespace tpr\command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use tpr\Console;

class Create extends Console
{
    protected function configure()
    {
        $this->setName('create')
            ->setDescription('create new tpr app')
            ->addArgument('app_name', InputArgument::REQUIRED)
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, getcwd());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        unset($input, $output);

        $output = $this->input->getOption('output');

        // todo :: create application
    }
}

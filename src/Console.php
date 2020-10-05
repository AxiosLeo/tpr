<?php

declare(strict_types=1);

namespace tpr;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use tpr\traits\CommandTrait;

class Console extends Command
{
    use CommandTrait;

    protected InputInterface $input;

    protected SymfonyStyle $output;

    private static InputInterface $inputHandle;

    private static SymfonyStyle $outputHandle;

    public static function input(): InputInterface
    {
        return self::$inputHandle;
    }

    public static function output(): SymfonyStyle
    {
        return self::$outputHandle;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        self::initIO($input, $output);
        $this->input  = self::input();
        $this->output = self::output();
        unset($input, $output);
    }

    private static function initIO(InputInterface $input, OutputInterface $output)
    {
        self::$inputHandle  = $input;
        self::$outputHandle = new SymfonyStyle($input, $output);
    }
}

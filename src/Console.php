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

    protected ?InputInterface $input = null;

    protected ?SymfonyStyle $output = null;

    private static ?InputInterface $inputHandle = null;

    private static ?SymfonyStyle $outputHandle = null;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        if (null !== self::$inputHandle) {
            $this->input = self::$inputHandle;
        }
        if (null !== self::$outputHandle) {
            $this->output = self::$outputHandle;
        }
    }

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

<?php

namespace tpr;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Console extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var SymfonyStyle
     */
    protected $output;

    private static $inputHandle;
    private static $outputHandle;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        self::initIO($input, $output);
        unset($input, $output);
    }

    /**
     * @return InputInterface
     */
    public static function input()
    {
        return self::$inputHandle;
    }

    /**
     * @return SymfonyStyle
     */
    public static function output()
    {
        return self::$outputHandle;
    }

    private static function initIO(InputInterface $input, OutputInterface $output)
    {
        self::$inputHandle  = $input;
        self::$outputHandle = new SymfonyStyle($input, $output);
    }
}

<?php

namespace tpr\command;

use Exception;
use Nette\PhpGenerator\Parameter;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use tpr\App;
use tpr\Console;
use tpr\Files;
use tpr\Path;
use Whoops\Handler\PlainTextHandler;
use Whoops\Run;

/**
 * Class Make.
 *
 * @description need require `nette/php-generator` composer library
 */
class Make extends Console
{
    protected function configure()
    {
        $this->setName('make')
            ->setDescription('generate code of command')
            ->addArgument('CommandName')
            ->addOption('namespace');
    }

    /**
     * @throws Exception
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        unset($input, $output);

        $command_name = $this->input->getArgument('CommandName');
        if (empty($command_name)) {
            $this->output->error('command name cannot be empty');
            die();
        }

        $class_name = ucfirst($command_name);
        $class_name = str_replace(['-'], '', $class_name);

        $custom_type = $this->output->ask('layer Name', '');
        if ($custom_type) {
            $namespace = App::drive()->getConfig()->namespace . '\\' . $custom_type;
            $save_path = Path::command() . $custom_type . '/' . $class_name . '.php';
        } else {
            $namespace = App::drive()->getConfig()->namespace;
            $save_path = Path::command() . '/' . $class_name . '.php';
        }

        if (file_exists($save_path)) {
            $confirm = $this->output->confirm($save_path . ' 已存在, 是否覆盖?', false);
            if (!$confirm) {
                exit(1);
            }
        }

        try {
            $namespace = new PhpNamespace($namespace);
            $namespace->addUse('\\tpr\\Console');
            $namespace->addUse('Symfony\\Component\\Console\\Input\\InputInterface');
            $namespace->addUse('Symfony\\Component\\Console\\Output\\OutputInterface');

            $Command = $namespace->addClass($class_name);
            $Command->addExtend(Console::class);
            $Command->addMethod('configure')->setBody('
$this->setName("' . $command_name . '")->setDescription(\'this is ' . $command_name . ' command\');
        ');
            $inputParam = new Parameter('input');
            $inputParam->setType('Symfony\\Component\\Console\\Input\\InputInterface');
            $outputParam = new Parameter('output');
            $outputParam->setType('Symfony\\Component\\Console\\Output\\OutputInterface');
            $Command->addMethod('execute')->setParameters([$inputParam, $outputParam])->setBody('
parent::execute($input, $output);
unset($input, $output);

$this->output->writeln("this is ' . $command_name . ' command");
        ');

            $printer = new PsrPrinter();
            $content = $printer->printNamespace($namespace);
            Files::save($save_path, "<?php\n\n" . $content);

            $this->output->success('Done! Save Path : ' . $save_path);
        } catch (Exception $e) {
            $whoops = new Run();
            $whoops->pushHandler(new PlainTextHandler());
            $whoops->register();

            throw $e;
        }
    }
}

<?php

declare(strict_types=1);

namespace tpr\core\response;

use tpr\App;
use tpr\Container;
use tpr\core\Dispatch;
use tpr\exception\FileNotFoundException;
use tpr\Path;
use Twig\TwigFunction;

class Html extends ResponseAbstract
{
    public string    $content_type = 'text/html';

    public function __construct()
    {
        $this->template_driver = Container::template();
        if (!App::debugMode()) {
            $this->template_driver->setCache(Path::join(Path::cache(), 'views'));
        }
    }

    /**
     * @param null $data
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function output($data = null): string
    {
        if (!empty($data)) {
            return $data;
        }
        $template = $this->options->views_path;
        if ('' === $template) {
            /** @var Dispatch $dispatch */
            $dispatch = Container::get('cgi_dispatch');
            $dir      = Path::join($dispatch->getModuleName(), $dispatch->getControllerName()) . \DIRECTORY_SEPARATOR;
            $file     = $dispatch->getActionName();
        } elseif (false !== strpos($template, ':')) {
            $tmp  = explode(':', $template);
            $file = array_pop($tmp);
            $dir  = Path::join(...$tmp);
            unset($tmp);
        } else {
            $dir  = \DIRECTORY_SEPARATOR;
            $file = $template;
        }
        unset($template);

        return $this->render($dir, $file, $this->options->params);
    }

    /**
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     *
     * @return string
     */
    private function render(string $dir, string $file, array $params = [])
    {
        $template_file = $dir . $file . '.' . $this->options->template_file_ext;
        if (!file_exists($template_file)) {
            throw new FileNotFoundException($template_file);
        }
        $driver = Container::template();
        foreach ($this->options->template_func as $name => $func) {
            $driver->addFunction(new TwigFunction($name, $func));
        }

        return $driver->render($dir . $file . '.' . $this->options->template_file_ext, $params);
    }
}

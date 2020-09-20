<?php

declare(strict_types=1);

namespace tpr\core\response;

use tpr\App;
use tpr\Container;
use tpr\core\Dispatch;
use tpr\Path;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class Html extends ResponseAbstract
{
    public string    $content_type = 'text/html';

    private Environment $template_driver;

    public function __construct()
    {
        $template_config = [];
        if (!App::debugMode()) {
            $template_config['cache'] = Path::join(Path::cache(), 'views');
        }
        $this->template_driver = new Environment(new FilesystemLoader(Path::views()), $template_config);
        $this->template_driver->addGlobal('lang', Container::get('lang'));
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
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     *
     * @return string
     */
    private function render(string $dir, string $file, array $params = [])
    {
        if (!empty($this->options->template_func)) {
            foreach ($this->options->template_func as $name => $func) {
                $this->template_driver->addFunction(new TwigFunction($name, $func));
            }
        }

        return $this->template_driver->render($dir . $file . '.' . $this->options->template_file_ext, $params);
    }
}

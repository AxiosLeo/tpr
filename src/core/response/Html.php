<?php

declare(strict_types=1);

namespace tpr\core\response;

use tpr\Container;
use tpr\core\Dispatch;
use Twig\TwigFunction;

class Html extends ResponseAbstract
{
    public string $content_type = 'text/html';

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
        $tmpl_path = $this->options->views_path;
        if ('' === $tmpl_path) {
            /** @var Dispatch $dispatch */
            $dispatch = Container::get('cgi_dispatch');
            $dir      = path_join($dispatch->getModuleName(), $dispatch->getControllerName()) . \DIRECTORY_SEPARATOR;
            $file     = $dispatch->getActionName();
        } elseif (false !== strpos($tmpl_path, ':')) {
            $tmp  = explode(':', $tmpl_path);
            $file = array_pop($tmp);
            $dir  = path_join(...$tmp);
            unset($tmp);
        } else {
            $dir  = \DIRECTORY_SEPARATOR;
            $file = $tmpl_path;
        }
        unset($template);

        return $this->render($dir, $file, $this->options->params);
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private function render(string $dir, string $file, array $params = []): string
    {
        $driver = Container::template();
        foreach ($this->options->template_func as $name => $func) {
            $driver->addFunction(new TwigFunction($name, $func));
        }
        $template_file = $dir . $file . '.' . $this->options->template_file_ext;

        return $driver->render($template_file, $params);
    }
}

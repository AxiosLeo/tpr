<?php

declare(strict_types=1);

namespace tpr\core\response;

use tpr\Container;
use tpr\core\Dispatch;
use tpr\core\Template;
use tpr\Path;

class Html extends ResponseAbstract
{
    public string    $content_type = 'text/html';

    private Template $template_driver;

    public function __construct()
    {
        $this->template_driver = Container::template();
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

        return $this->template_driver->render($dir, $file, $this->options->params);
    }
}

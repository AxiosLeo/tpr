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
    protected string $name         = 'html';

    protected array $options = [
        'params'     => [],
        'views_path' => '',
    ];

    private ?Template $template_driver = null;

    /**
     * @return null|mixed|Template
     */
    public function getTemplateDriver()
    {
        if (null === $this->template_driver) {
            $this->template_driver = Container::template();
        }

        return $this->template_driver;
    }

    /**
     * @param null $data
     *
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     *
     * @return string
     */
    public function output($data = null)
    {
        if (!empty($data)) {
            return $data;
        }
        $template = $this->options['views_path'];
        if ('' === $template) {
            return '';
        }
        if (null === $template) {
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

        return $this->getTemplateDriver()->render($dir, $file, $this->options['params']);
    }
}

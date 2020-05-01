<?php

declare(strict_types=1);

namespace tpr\core\response;

use tpr\Container;
use tpr\core\Dispatch;
use tpr\core\Template;
use tpr\Path;

class Html extends ResponseAbstract
{
    public $content_type    = 'text/html';
    protected $name         = 'html';

    protected $options = [
        'params'     => [],
        'views_path' => '',
    ];

    /**
     * @var Template
     */
    private $template_driver;

    /**
     * @return null|mixed|Template
     */
    public function getTemplateDriver()
    {
        if (null === $this->template_driver) {
            $this->template_driver = Container::get('template');
        }

        return $this->template_driver;
    }

    /**
     * @param null $data
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @return string
     */
    public function output($data = null)
    {
        if (!empty($data)) {
            return $data;
        }
        $template = $this->options['views_path'];
        if (empty($template)) {
            /** @var Dispatch $dispatch */
            $dispatch = Container::get('cgi_dispatch');
            $dir      = Path::dir([
                $dispatch->getModuleName(), $dispatch->getControllerName(),
            ]);
            $file     = $dispatch->getActionName();
        } elseif (false !== strpos($template, ':')) {
            $tmp  = explode(':', $template);
            $file = array_pop($tmp);
            $dir  = Path::format(Path::dir($tmp));
            unset($tmp);
        } else {
            $dir  = \DIRECTORY_SEPARATOR;
            $file = $template;
        }
        unset($template);

        return $this->getTemplateDriver()->render($dir, $file, $this->options['params']);
    }
}

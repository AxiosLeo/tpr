<?php

namespace tpr\core\response;

use tpr\Container;
use tpr\core\Template;
use tpr\Path;

class Html extends ResponseAbstract
{
    protected $name = 'html';

    protected $options = [
        'params'     => [],
        'views_path' => '',
    ];

    public $content_type = 'text/html';

    /**
     * @var Template
     */
    private $template_driver;

    public function getTemplateDriver()
    {
        if (is_null($this->template_driver)) {
            $this->template_driver = Container::get('template');
        }

        return $this->template_driver;
    }

    public function output($data = null)
    {
        if (!empty($data)) {
            return $data;
        }
        $template = $this->options['views_path'];
        $dir      = '';
        $file     = '';
        if (empty($template)) {
            $dispatch = Container::app()->getDispatch();
            $dir      = Path::dir([
                $dispatch->getModuleName(), $dispatch->getControllerName(),
            ]);
            $file     = $dispatch->getActionName();
        } elseif (false !== strpos($template, ':')) {
            list($dir, $file) = explode(':', $template);
            $dir              = Path::format($dir);
        } else {
            $dir  = DIRECTORY_SEPARATOR;
            $file = $template;
        }

        return $this->getTemplateDriver()->render($dir, $file, $this->options['params']);
    }
}

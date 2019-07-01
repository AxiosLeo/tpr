<?php

namespace tpr\core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Template
{
    private $options = [
        'ext'  => 'html',
        'base' => '',
    ];

    private $base_dir;

    private $template_loader;

    public function __construct()
    {
        $options = \tpr\Config::get('views', []);

        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->setBaseDir($this->options['base']);
    }

    public function setBaseDir($base_dir = null)
    {
        if (empty($base_dir)) {
            $base_dir = \tpr\Path::views();
        }
        $this->base_dir = \tpr\Path::format($base_dir);

        return $this;
    }

    public function getExt()
    {
        $ext = $this->options['ext'];
        if (false === strpos($ext, '.')) {
            $ext = '.' . $ext;
        }

        return $ext;
    }

    public function render($dir, $file, $params = [])
    {
        if (is_null($this->template_loader)) {
            $this->template_loader = new Environment(new FilesystemLoader($this->base_dir), [
                'cache' => \tpr\Path::cache(),
            ]);
        }
        return $this->template_loader->render($dir . $file . $this->getExt(), $params);
    }
}

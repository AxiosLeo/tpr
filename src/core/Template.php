<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\App;
use tpr\Container;
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
        $this->options = \tpr\Config::get('views', $this->options);
        $this->setBaseDir($this->options['base']);
    }

    /**
     * @param null $base_dir
     *
     * @return $this
     */
    public function setBaseDir($base_dir = null)
    {
        if (empty($base_dir)) {
            $base_dir = \tpr\Path::views();
        }
        $this->base_dir = \tpr\Path::format($base_dir);

        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getExt()
    {
        $ext = $this->options['ext'];
        if (false === strpos($ext, '.')) {
            $ext = '.' . $ext;
        }

        return $ext;
    }

    /**
     * @param       $dir
     * @param       $file
     * @param array $params
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     *
     * @return string
     */
    public function render($dir, $file, $params = [])
    {
        return $this->driver()->render($dir . $file . $this->getExt(), $params);
    }

    /**
     * @throws \Exception
     *
     * @return Environment
     */
    public function driver()
    {
        if (null === $this->template_loader) {
            $template_config          = \tpr\Config::get('template', []);
            $template_config['cache'] = App::client()->debug() ? false : \tpr\Path::cache();
            $this->template_loader    = new Environment(new FilesystemLoader($this->base_dir), $template_config);
            $this->template_loader->addGlobal('lang', Container::get('lang'));
        }

        return $this->template_loader;
    }
}

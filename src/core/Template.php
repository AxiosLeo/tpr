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
        'base' => null,
    ];

    private ?string $base_dir;

    private Environment $template_loader;

    public function __construct()
    {
        $this->options = \tpr\Config::get('views', $this->options);
        $this->setBaseDir($this->options['base']);
    }

    public function setBaseDir(string $base_dir = null)
    {
        if (empty($base_dir)) {
            $base_dir = \tpr\Path::views();
        }
        $this->base_dir = $base_dir;

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
     * @param $dir
     * @param $file
     *
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     * @throws \Exception
     *
     * @return string
     */
    public function render(string $dir, string $file, array $params = [])
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
            $template_config['cache'] = App::debugMode() ? false : \tpr\Path::cache();
            $this->template_loader    = new Environment(new FilesystemLoader($this->base_dir), $template_config);
            $this->template_loader->addGlobal('lang', Container::get('lang'));
        }

        return $this->template_loader;
    }
}

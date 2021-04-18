<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\App;
use tpr\Event;
use tpr\Path;

final class Lang
{
    private array $dist = [];

    private string $default_lang_set;

    private array $files = [];

    public function __construct()
    {
        $this->default_lang_set = App::drive()->getConfig()->lang;
        if (file_exists(Path::langs())) {
            $files = \axios\tools\Files::search(Path::langs(), ['php']);
            foreach ($files as $filepath) {
                $lang_set               = basename($filepath, '.' . pathinfo($filepath, \PATHINFO_EXTENSION));
                $this->files[$lang_set] = $filepath;
            }
        }
    }

    public function tran(string $word, ?string $lang_set_name = null): string
    {
        Event::listen('lang_translate', $word);
        if (null === $lang_set_name) {
            $lang_set_name = $this->default_lang_set;
        }
        if (!isset($this->dist[$lang_set_name])) {
            if (!isset($this->files[$lang_set_name])) {
                return $word;
            }
            $this->dist[$lang_set_name] = require_once $this->files[$lang_set_name];
        }
        if (isset($this->dist[$lang_set_name][$word])) {
            return $this->dist[$lang_set_name][$word];
        }

        return $word;
    }

    /**
     * @throws \Exception
     */
    public function load(string $lang_set_name, string $file, bool $throw_exception = false): void
    {
        try {
            $dist = require_once $file;
            if (isset($this->dist[$lang_set_name]) && !empty($this->dist[$lang_set_name])) {
                $this->dist[$lang_set_name] = array_merge($this->dist[$lang_set_name], $dist);
            } else {
                $this->dist[$lang_set_name] = $dist;
            }
        } catch (\Exception $e) {
            if ($throw_exception) {
                throw $e;
            }
        }
    }
}

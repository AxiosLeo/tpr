<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\App;
use tpr\Event;

class Lang
{
    private array $dist = [];

    private string $default_lang_set;

    public function __construct()
    {
        $this->default_lang_set = App::drive()->getConfig()->lang;
    }

    public function tran(string $word, ?string $lang_set_name = null): string
    {
        Event::listen('lang_translate', $word);
        if (null === $lang_set_name) {
            $lang_set_name = $this->default_lang_set;
        }
        if (!isset($this->dist[$lang_set_name])) {
            $dist = \tpr\Config::get('lang.data.' . $lang_set_name);
            if (null === $dist) {
                return $word;
            }
            $this->dist[$lang_set_name] = $dist;
        }
        if (isset($this->dist[$lang_set_name][$word])) {
            return $this->dist[$lang_set_name][$word];
        }

        return $word;
    }

    /**
     * @throws \Exception
     */
    public function load(string $lang_set_name, string $file, bool $ignore_exception = true): void
    {
        try {
            $dist = require_once $file;
            if (isset($this->dist[$lang_set_name]) && !empty($this->dist[$lang_set_name])) {
                $this->dist[$lang_set_name] = array_merge($this->dist[$lang_set_name], $dist);
            } else {
                $this->dist[$lang_set_name] = $dist;
            }
        } catch (\Exception $e) {
            if (!$ignore_exception) {
                throw $e;
            }
        }
    }
}

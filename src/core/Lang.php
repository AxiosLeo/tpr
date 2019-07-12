<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\Cache;
use tpr\Event;

class Lang
{
    private const LANG_CACHE_KEY = 'tpr_lang';
    private $word                = [];

    private $langDefault = '';

    private $langDir = '';

    public function __construct()
    {
        $this->langDefault = \tpr\App::lang();
        $this->langDir     = \tpr\Path::lang();
    }

    public function load(string $langSet, array $word): void
    {
        if (empty($word)) {
            return;
        }
        if (!isset($this->word[$langSet])) {
            $this->word[$langSet] = [];
        }

        if (empty($this->word[$langSet])) {
            $this->word[$langSet] = $word;
        } else {
            $this->word[$langSet] = array_merge($this->word[$langSet], $word);
        }
    }

    public function tran($data, $langSet = null)
    {
        $tmp = $data;
        Event::listen('lang_translate', $tmp);
        if ($tmp !== $data) {
            return $tmp;
        }
        if (null === $langSet) {
            $langSet = $this->langDefault;
        }
        $this->loadCache();
        if (!isset($this->word[$langSet])) {
            if (!file_exists($this->langDir)) {
                return $data;
            }
            $config_file_list = \tpr\Files::searchAllFiles($this->langDir, ['php']);

            if (empty($config_file_list)) {
                return $data;
            }
            $config_file_list = array_flip($config_file_list);
            if (isset($config_file_list[$langSet])) {
                $this->word[$langSet] = \Noodlehaus\Config::load($config_file_list[$langSet])->all();
            }
            $this->setCache();
        }
        if (!isset($this->word[$langSet])) {
            return $data;
        }
        if (isset($this->word[$langSet][$data])) {
            return $this->word[$langSet][$data];
        }

        return $data;
    }

    private function loadCache()
    {
        if (empty($this->word)) {
            if (true === \tpr\App::debug() || !Cache::contains(self::LANG_CACHE_KEY)) {
                return;
            }

            $this->word = Cache::fetch(self::LANG_CACHE_KEY);
        }
    }

    private function setCache()
    {
        if (!\tpr\App::debug()) {
            Cache::save(self::LANG_CACHE_KEY, $this->word, \tpr\App::options('cache_time'));
        }

        return true;
    }
}

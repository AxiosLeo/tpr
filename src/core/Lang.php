<?php

declare(strict_types=1);

namespace tpr\core;

use tpr\App;
use tpr\Event;
use tpr\Path;

class Lang
{
    private array $word = [];

    private string $langDefault;

    private string $langDir;

    private array $langFiles = [];

    public function __construct()
    {
        $this->langDefault = App::drive()->getConfig()->lang;
        $this->langDir     = Path::lang();
        $this->load();
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
        if (!isset($this->word[$langSet])) {
            return $data;
        }
        if ([] === $this->word[$langSet]) {
            $this->word[$langSet] = \Noodlehaus\Config::load($this->langFiles[$langSet])->all();
        }
        if (isset($this->word[$langSet][$data])) {
            return $this->word[$langSet][$data];
        }

        return $data;
    }

    private function load(): void
    {
        if (!file_exists($this->langDir)) {
            return;
        }
        $fileList = \tpr\Files::search($this->langDir, ['php']);
        if ([] === $fileList) {
            return;
        }
        $fileList = array_flip($fileList);
        foreach ($fileList as $langSet) {
            $this->word[$langSet] = [];
            $this->langFiles      = $fileList[$langSet];
        }
    }
}

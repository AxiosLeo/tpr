<?php

declare(strict_types=1);

namespace tpr\core;

use axios\tools\ArrayMap;
use Noodlehaus\Parser\Ini;
use tpr\exception\FileNotFoundException;
use tpr\Path;

class Env
{
    private ArrayMap $env;

    public function __construct()
    {
        $this->env = new ArrayMap();
        $this->load('.env');
    }

    public function load(string $file): void
    {
        $env_path = path_join(Path::root(), $file);
        if (file_exists($env_path)) {
            $env = \Noodlehaus\Config::load($env_path, new Ini())->all();
            if (\is_array($env)) {
                $this->env->set($env, null);
            }
        } else {
            throw new FileNotFoundException($file);
        }
    }

    public function get($key = null, $default = null)
    {
        if (null === $key) {
            return $this->env->get();
        }
        $val = $this->env->get($key);
        if (null !== $val) {
            return $val;
        }
        $env_key = implode('_', explode('.', $key));
        $val     = getenv(strtoupper($env_key));
        if (false === $val) {
            return $default;
        }
        $this->env->set($key, $val);

        return $val;
    }
}

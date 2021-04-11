<?php

declare(strict_types=1);

namespace tpr;

/**
 * Class Files.
 *
 * @deprecated will deprecated on 5.1 version
 */
class Files
{
    /**
     * browse files&finder.
     */
    public static function browse(string $dir, bool $realpath = false, bool $asc = true, int $sorting_type = \SORT_FLAG_CASE): array
    {
        return \tpr\functions\fs\browse($dir, $realpath, $asc, $sorting_type);
    }

    /**
     * search files.
     */
    public static function search(string $dir, ?array $extInclude = null, bool $asc = true, int $sorting_type = \SORT_FLAG_CASE): array
    {
        return \tpr\functions\fs\search($dir, $extInclude, $asc, $sorting_type);
    }

    /**
     * @param string $source copy from path
     * @param string $target copy to path
     * @param bool   $force  force copy if file exist when $force is true
     */
    public static function copy(string $source, string $target, bool $force = false, ?array $extInclude = null): void
    {
        \tpr\functions\fs\copy($source, $target, $force, $extInclude);
    }

    public static function save(string $filename, string $text, int $blank = 0): void
    {
        \tpr\functions\fs\write($filename, $text, 'a+', $blank);
    }

    public static function append($filename, $text, $blank = 0): void
    {
        \tpr\functions\fs\write($filename, $text, 'a+', $blank);
    }

    public static function remove(string $path): void
    {
        \tpr\functions\fs\remove($path);
    }
}

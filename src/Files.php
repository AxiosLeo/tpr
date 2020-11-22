<?php

declare(strict_types=1);

namespace tpr;

/**
 * Class Files.
 */
class Files
{
    /**
     * browse files&finder.
     *
     * @return array
     */
    public static function browse(string $dir, bool $realpath = false, bool $asc = true, int $sorting_type = SORT_FLAG_CASE)
    {
        $list = [];
        if (is_dir($dir)) {
            $dirHandle = opendir($dir);
            while (!\is_bool($dirHandle) && false !== ($file_name = readdir($dirHandle))) {
                if ('..' === $file_name || '.' === $file_name) {
                    continue;
                }
                if ($realpath) {
                    $file_name = Path::join($dir, $file_name);
                } else {
                    $real = Path::join($dir, $file_name);
                    if (is_dir($real)) {
                        $file_name .= \DIRECTORY_SEPARATOR;
                    }
                }
                array_push($list, $file_name);
            }
        }
        $asc ? sort($list, $sorting_type) : rsort($list, $sorting_type);

        return array_values($list);
    }

    /**
     * search files.
     */
    public static function search(string $dir, ?array $extInclude = null, bool $asc = true, int $sorting_type = SORT_FLAG_CASE): array
    {
        $list = [];
        if (is_dir($dir)) {
            $dirHandle = opendir($dir);
            while (!\is_bool($dirHandle) && false !== ($file_name = readdir($dirHandle))) {
                $tmp = str_replace('.', '', $file_name);
                if ('' != $tmp) {
                    $subFile = Path::join($dir, $file_name);
                    $ext     = pathinfo($file_name, PATHINFO_EXTENSION);
                    if (is_dir($subFile)) {
                        $list = array_merge($list, self::search($subFile, $extInclude, $asc, $sorting_type));
                    } elseif (\is_array($extInclude) && \in_array($ext, $extInclude)) {
                        array_push($list, $subFile);
                    } elseif (null === $extInclude) {
                        array_push($list, $subFile);
                    }
                }
            }
            closedir($dirHandle);
        }
        $asc ? sort($list, $sorting_type) : rsort($list, $sorting_type);

        return array_values($list);
    }

    public static function save(string $filename, string $text, int $blank = 0): void
    {
        self::write($filename, $text, 'w', $blank);
    }

    public static function append($filename, $text, $blank = 0): void
    {
        self::write($filename, $text, 'a+', $blank);
    }

    public static function remove(string $path): void
    {
        if (is_dir($path)) {
            $handle = opendir($path);
            while (false !== ($fileName = readdir($handle))) {
                $subFile = Path::join($path, $fileName);
                $tmp     = str_replace('.', '', $fileName);
                if ('' != $tmp && is_dir($subFile)) {
                    self::remove($subFile);
                } elseif ('' != $tmp && !is_dir($subFile)) {
                    @unlink($subFile);
                }
            }
            closedir($handle);
            @rmdir($path);
        } else {
            @unlink($path);
        }
    }

    private static function write(string $filename, string $text, string $mode, int $blank = 0): void
    {
        if (!file_exists(\dirname($filename))) {
            @mkdir(\dirname($filename), 0755, true);
        }
        $fp = fopen($filename, $mode);
        if (flock($fp, LOCK_EX)) {
            while ($blank > 0) {
                fwrite($fp, PHP_EOL);
                $blank = $blank - 1;
            }
            fwrite($fp, $text . PHP_EOL);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
}

<?php

declare(strict_types=1);

namespace tpr;

/**
 * Class Files.
 */
class Files
{
    /**
     * search files.
     *
     * @return array
     */
    public static function search(string $dir, array $extInclude = ['php'], bool $asc = false, int $sorting_type = SORT_FLAG_CASE)
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
                    } elseif (\is_string($extInclude)) {
                        if ('*' == $extInclude || preg_match($extInclude, $file_name)) {
                            $list[\count($list)] = $subFile;
                        }
                    } elseif (\is_array($extInclude) && \in_array($ext, $extInclude)) {
                        $list[\count($list)] = $subFile;
                    }
                }
            }
            closedir($dirHandle);
        }
        $asc ? ksort($list, $sorting_type) : krsort($list, $sorting_type);

        return $list;
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

<?php

namespace tpr\core;

class Files
{
    public function readJsonFile($filename, $is_array = true)
    {
        if (!file_exists($filename)) {
            return null;
        }
        $content = file_get_contents($filename);

        return $is_array ? json_decode($content, true) : $content;
    }

    public function searchFile($dir, $extArray = [], $exclude = [])
    {
        $list = [];
        if (is_dir($dir)) {
            $dir       = \tpr\Path::format($dir);
            $dirHandle = opendir($dir);
            while (false !== ($file_name = readdir($dirHandle))) {
                if (\in_array($file_name, $exclude)) {
                    continue;
                }
                $subFile = $dir . $file_name;
                $tmp     = str_replace('.', '', $file_name);
                $ext     = pathinfo($file_name, PATHINFO_EXTENSION);
                if (!is_dir($subFile) && '' != $tmp && \in_array($ext, $extArray)) {
                    $list[$file_name] = $subFile;
                }
            }
            closedir($dirHandle);
        }

        return $list;
    }

    public function searchDir($dir, $exclude = [])
    {
        $list = [];
        if (is_dir($dir)) {
            $dir       = \tpr\Path::format($dir);
            $dirHandle = opendir($dir);
            while (false !== ($file_name = readdir($dirHandle))) {
                $subFile = $dir . $file_name;
                $tmp     = str_replace('.', '', $file_name);
                if (is_dir($subFile) && '' != $tmp && !\in_array($file_name, $exclude)) {
                    $list[$file_name] = $subFile;
                }
            }
            closedir($dirHandle);
        }

        return $list;
    }

    /**
     * @param string       $dir
     * @param array|string $extInclude
     * @param bool         $asc
     * @param int          $sorting_type
     *
     * @return array
     */
    public function searchAllFiles($dir, $extInclude = '*', $asc = false, $sorting_type = SORT_FLAG_CASE)
    {
        $list = [];
        if (is_dir($dir)) {
            $dir       = \tpr\Path::format($dir);
            $dirHandle = opendir($dir);
            while (!\is_bool($dirHandle) && false !== ($file_name = readdir($dirHandle))) {
                $tmp = str_replace('.', '', $file_name);
                if ('' != $tmp) {
                    $subFile   = $dir . $file_name;
                    $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
                    $file_name = basename($file_name, '.' . $ext);
                    if (is_dir($subFile)) {
                        $list = array_merge($list, self::searchAllFiles($subFile, $extInclude));
                    } elseif (\is_string($extInclude)) {
                        if ('*' == $extInclude) {
                            $list[$subFile] = $file_name;
                        } elseif ($extInclude == $ext) {
                            $list[$subFile] = $file_name;
                        }
                    } elseif (\is_array($extInclude) && \in_array($ext, $extInclude)) {
                        $list[$subFile] = $file_name;
                    }
                }
            }
            closedir($dirHandle);
        }
        $asc ? ksort($list, $sorting_type) : krsort($list, $sorting_type);

        return $list;
    }

    public function append($filename, $text, $blank = 0)
    {
        if (!file_exists(\dirname($filename))) {
            @mkdir(\dirname($filename));
        }
        $fp = fopen($filename, 'a+');
        if (flock($fp, LOCK_EX)) {
            while ($blank > 0) {
                fwrite($fp, "\r\n");
                $blank = $blank - 1;
            }
            fwrite($fp, $text . "\r\n");
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    public function save($filename, $text, $blank = 0)
    {
        if (!file_exists(\dirname($filename))) {
            @mkdir(\dirname($filename), 0777, true);
        }
        $fp = fopen($filename, 'w');
        if (flock($fp, LOCK_EX)) {
            while ($blank > 0) {
                fwrite($fp, "\r\n");
                $blank = $blank - 1;
            }
            fwrite($fp, $text . "\r\n");
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    public function delete($path)
    {
        if (is_dir($path)) {
            $path   = \tpr\Path::format($path);
            $handle = opendir($path);
            while (false !== ($fileName = readdir($handle))) {
                $subFile = $path . \DIRECTORY_SEPARATOR . $fileName;
                $tmp     = str_replace('.', '', $fileName);
                if ('' != $tmp && is_dir($subFile)) {
                    $this->delete($subFile);
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
}

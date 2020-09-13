<?php

declare(strict_types=1);

namespace tpr\library;

class ArrayMap
{
    private array $array;

    private string $separator;

    public function __construct(array $array = [], string $separator = '.')
    {
        $this->array     = $array;
        $this->separator = $separator;
    }

    /**
     * 设置任意层级子元素.
     *
     * @param array|int|string $key
     * @param mixed            $value
     *
     * @return ArrayMap
     */
    public function set($key, $value = null)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            $recurArrayChange = function ($array, $keyArr, $value) use (&$recurArrayChange) {
                $key = array_shift($keyArr);
                if (null === $key) {
                    return $value;
                }
                if (!isset($array[$key])) {
                    $array[$key] = [];
                }
                $array[$key] = $recurArrayChange($array[$key], $keyArr, $value);

                return $array;
            };

            $keyArray    = explode($this->separator, trim($key, ' .'));
            $this->array = $recurArrayChange($this->array, $keyArray, $value);
        }

        return $this;
    }

    public function all(): array
    {
        return $this->array;
    }

    /**
     * 获取任意层级子元素.
     *
     * @param null|int|string $key
     * @param mixed           $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if (null === $key) {
            return $this->array;
        }

        if (false === strpos($key, $this->separator)) {
            return isset($this->array[$key]) ? $this->array[$key] : $default;
        }

        $keyArray = explode($this->separator, $key);
        $tmp      = $this->array;
        foreach ($keyArray as $k) {
            if (isset($tmp[$k])) {
                $tmp = $tmp[$k];
            } else {
                $tmp = $default;

                break;
            }
        }

        return $tmp;
    }

    /**
     * 删除任意层级子元素.
     *
     * @param array|int|string $key
     *
     * @return ArrayMap
     */
    public function delete($key)
    {
        if (\is_array($key)) {
            foreach ($key as $k) {
                $this->set($k, null);
            }
        } else {
            $this->set($key, null);
        }

        return $this;
    }
}

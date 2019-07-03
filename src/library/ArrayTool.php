<?php

namespace tpr\library;

use ArrayAccess;
use Closure;

/**
 * 数组操作类.
 *
 * @desc 支持任意层级子元素的增删改查
 */
class ArrayTool implements ArrayAccess
{
    private $array;

    private $separator;

    public function __construct($array, $separator = '.')
    {
        $this->array     = $array;
        $this->separator = $separator;
    }

    public static function instance($array = [], $separator = '.')
    {
        return new self($array, $separator);
    }

    /**
     * 数组过滤.
     *
     * @desc 可自定义排除过滤
     *
     * @param $array
     * @param string $except
     * @param bool   $reset_key 是否重置键名
     *
     * @return mixed
     */
    public function filter($array, $except = '', $reset_key = false)
    {
        // $except = 'number|null|string'

        $except = explode('|', $except);
        if (empty($except)) {
            $array = array_filter($array);

            return $reset_key ? array_values($array) : $array;
        }

        foreach ($array as $k => $v) {
            if (is_numeric($v) && \in_array('number', $except)) {
                continue;
            }

            if (\is_string($v) && \in_array('string', $except)) {
                continue;
            }

            if (null === $v && \in_array('null', $except)) {
                continue;
            }
            if (empty($v)) {
                unset($array[$k]);
            }
        }

        return $reset_key ? array_values($array) : $array;
    }

    /**
     * 设置任意层级子元素.
     *
     * @param array|int|string $key
     * @param mixed            $value
     *
     * @return $this
     */
    public function set($key, $value = null)
    {
        if (\is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            $keyArray    = $this->filter(explode($this->separator, $key), 'number', true);
            $this->array = $this->recurArrayChange($this->array, $keyArray, $value);
        }

        return $this;
    }

    /**
     * 获取任意层级子元素.
     *
     * @param null|int|string $key
     * @param Closure|mixed   $default
     *
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if (null === $key) {
            return $this->array;
        }

        if (false === strpos($key, $this->separator)) {
            return isset($this->array[$key]) ? $this->array[$key] : $this->defaultValue($key, $default);
        }

        $keyArray = explode($this->separator, $key);
        $tmp      = $this->array;
        foreach ($keyArray as $k) {
            if (isset($tmp[$k])) {
                $tmp = $tmp[$k];
            } else {
                $tmp = $this->defaultValue($key, $default);

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
     * @return $this
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

    /**
     * 正序排序.
     *
     * @param $key
     * @param string $rule
     * @param bool   $save_key
     *
     * @return $this
     */
    public function sort($key = null, $rule = '', $save_key = true)
    {
        $this->sortArray($key, $rule, 'asc', $save_key);

        return $this;
    }

    /**
     * 倒序排序.
     *
     * @param $key
     * @param string $rule
     * @param bool   $save_key
     *
     * @return $this
     */
    public function rSort($key = null, $rule = '', $save_key = true)
    {
        $this->sortArray($key, $rule, 'desc', $save_key);

        return $this;
    }

    /**
     * 获取某一节点下的子元素key列表.
     *
     * @param $key
     *
     * @return array
     */
    public function getChildKeyList($key = null)
    {
        $child = $this->get($key);
        $list  = [];
        $n     = 0;
        foreach ($child as $k => $v) {
            $list[$n++] = $k;
        }

        return $list;
    }

    /**
     * isset($array[$key]).
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return null !== $this->get($offset);
    }

    /**
     * $array[$key].
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * $array[$key] = $value.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return $this
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * unset($array[$key]).
     *
     * @param mixed $offset
     *
     * @return $this
     */
    public function offsetUnset($offset)
    {
        return $this->delete($offset);
    }

    private function defaultValue($key = null, $default = null)
    {
        if ($default instanceof Closure) {
            return $default($key);
        }

        return $default;
    }

    /**
     * 支持任意层级子元素的数组排序.
     *
     * @param mixed  $key
     * @param string $sortRule
     * @param string $order
     * @param bool   $save_key
     *
     * @return mixed
     */
    private function sortArray($key = null, $sortRule = '', $order = 'asc', $save_key = true)
    {
        $array = $this->get($key);

        if (!\is_array($array)) {
            return false;
        }

        /*
         * $array = [
         *              ["book"=>10,"version"=>10],
         *              ["book"=>19,"version"=>30],
         *              ["book"=>10,"version"=>30],
         *              ["book"=>19,"version"=>10],
         *              ["book"=>10,"version"=>20],
         *              ["book"=>19,"version"=>20]
         *      ];
         */
        if (\is_array($sortRule)) {
            // $sortRule = ['book'=>"asc",'version'=>"asc"];
            usort($array, function ($a, $b) use ($sortRule) {
                foreach ($sortRule as $sortKey => $order) {
                    if ($a[$sortKey] == $b[$sortKey]) {
                        continue;
                    }

                    return (('asc' != $order) ? -1 : 1) * (($a[$sortKey] < $b[$sortKey]) ? -1 : 1);
                }

                return 0;
            });
        } elseif (\is_string($sortRule)) {
            if (!empty($sortRule)) {
                /*
                 * $sortRule = "book";
                 * $order = "asc";
                 */
                usort($array, function ($a, $b) use ($sortRule, $order) {
                    if ($a[$sortRule] == $b[$sortRule]) {
                        return 0;
                    }

                    return (('asc' != $order) ? -1 : 1) * (($a[$sortRule] < $b[$sortRule]) ? -1 : 1);
                });
            } else {
                if ($save_key) {
                    'asc' == $order ? asort($array) : arsort($array);
                } else {
                    usort($array, function ($a, $b) use ($order) {
                        if ($a == $b) {
                            return 0;
                        }

                        return (('asc' != $order) ? -1 : 1) * (($a < $b) ? -1 : 1);
                    });
                }
            }
        }

        null === $key ? $this->array = $array : $this->set($key, $array);

        return $this->array;
    }

    /**
     * 递归遍历.
     *
     * @param array $array
     * @param array $keyArray
     * @param mixed $value
     *
     * @return array
     */
    private function recurArrayChange($array, $keyArray, $value = null)
    {
        $key0 = $keyArray[0];
        if (1 === \count($keyArray)) {
            $this->changeValue($array, $key0, $value);
        } elseif (\is_array($array) && isset($keyArray[1])) {
            unset($keyArray[0]);
            $keyArray = array_values($keyArray);
            if (!isset($array[$key0])) {
                $array[$key0] = [];
            }
            $array[$key0] = $this->recurArrayChange($array[$key0], $keyArray, $value);
        } else {
            $this->changeValue($array, $key0, $value);
        }

        return $array;
    }

    private function changeValue(&$array, $key, $value)
    {
        if (null === $value) {
            unset($array[$key]);
        } else {
            $array[$key] = $value;
        }
    }
}

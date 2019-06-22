<?php

namespace tpr\core;

use tpr\exception\ClassNotExistException;

class Hook
{
    private static $behaviors = [];

    /**
     * 批量添加行为，不支持自定义方法和操作置顶
     *
     * @param array $behaviors
     */
    public function import(array $behaviors): void
    {
        foreach ($behaviors as $behavior_name => $behavior) {
            if (is_string($behavior)) {
                $this->add($behavior_name, $behavior);
            } else if (is_array($behavior)) {
                foreach ($behavior as $behavior_item) {
                    $this->add($behavior_name, $behavior_item);
                }
            }
        }
    }

    /**
     * 添加行为
     *
     * @param string                 $behavior_name
     * @param string|Object|\Closure $behavior_class
     * @param string                 $behavior_method
     * @param array                  $params
     * @param bool                   $first
     */
    public function add(string $behavior_name, $behavior_class, string $behavior_method = "run", $params = [], $first = false): void
    {
        if (!isset(self::$behaviors[$behavior_name])) {
            self::$behaviors[$behavior_name] = [];
        }

        if (is_string($behavior_class) && !class_exists($behavior_class)) {
            throw new ClassNotExistException("Class Not Exist : " . $behavior_class, $behavior_class);
        }

        $behavior = [
            "class"  => $behavior_class,
            "method" => $behavior_method,
            "params" => $params
        ];

        $first ? array_unshift(self::$behaviors[$behavior_name], $behavior) : array_push(self::$behaviors[$behavior_name], $behavior);
    }

    /**
     * 监听行为
     *
     * @param string        $behavior_name
     * @param mixed         $data
     * @param \Closure|null $callback
     */
    public function listen(string $behavior_name, &$data = [], \Closure $callback = null): void
    {
        if (isset(self::$behaviors[$behavior_name])) {
            foreach (self::$behaviors[$behavior_name] as $behavior) {
                $this->exec($behavior, $data, $callback);
            }
        }
    }

    /**
     * 仅监听某个行为数组中的第一个
     *
     * @param string        $behavior_name
     * @param mixed         $data
     * @param \Closure|null $callback
     */
    public function listenFirst(string $behavior_name, &$data = [], \Closure $callback = null): void
    {
        if (isset(self::$behaviors[$behavior_name], self::$behaviors[$behavior_name][0])) {
            $this->exec(self::$behaviors[$behavior_name][0], $data, $callback);
        }
    }

    /**
     * 获取行为数组
     *
     * @param string|null $behavior_name
     *
     * @return array
     */
    public function get(string $behavior_name = null): array
    {
        if (is_null($behavior_name)) {
            return self::$behaviors;
        }

        return isset(self::$behaviors[$behavior_name]) ? self::$behaviors[$behavior_name] : [];
    }

    /**
     * 移除行为中的某个操作
     *
     * @param string $behavior_name
     * @param int    $index
     *
     * @return bool
     */
    public function remove(string $behavior_name, int $index): bool
    {
        if (isset(self::$behaviors[$behavior_name][$index])) {
            unset(self::$behaviors[$behavior_name][$index]);
            self::$behaviors[$behavior_name] = array_values(self::$behaviors[$behavior_name]);
            return true;
        }
        return false;
    }

    /**
     * 删除行为
     *
     * @param string $behavior_name
     *
     * @return bool
     */
    public function delete(string $behavior_name): bool
    {
        if (isset(self::$behaviors[$behavior_name])) {
            unset(self::$behaviors[$behavior_name]);
            return true;
        }
        return false;
    }

    /**
     * 执行某个行为
     *
     * @param               $behavior
     * @param mixed         $data
     * @param \Closure|null $callback
     */
    private function exec($behavior, &$data, \Closure $callback = null): void
    {
        $class       = $behavior["class"];
        $method      = $behavior["method"];
        $extra_param = $behavior["params"];
        if ($class instanceof \Closure) {
            $result = call_user_func_array($class, [&$data]);
        } else if (is_object($class)) {
            $result = call_user_func_array([$class, $method], [&$data, $extra_param]);
        } else {
            $obj    = new $class();
            $result = call_user_func_array([$obj, $method], [&$data, $extra_param]);
        }
        if (!is_null($callback)) {
            call_user_func_array($callback, [&$data, $result]);
        }
    }
}
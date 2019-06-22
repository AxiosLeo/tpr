<?php

namespace tpr\lib;

use IteratorAggregate;
use tpr\core\Config;
use tpr\exception\ClassNotExistException;

/**
 * Class Container
 *
 * @package tpr
 * @method void set($name, $class = null) static
 * @method Object get($name) static
 * @method bool has($name) static
 * @method void bind($name, $object = null) static
 *
 * @method Config config()
 */
final class Container implements \ArrayAccess, IteratorAggregate
{
    private static $instance;

    private static $class = [];

    private static $object = [];

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param string $name
     * @param string $class
     */
    protected function setInstance($name, $class = null)
    {
        if (is_array($name)) {
            foreach ($name as $instance_name => $class_name) {
                self::$class[$instance_name] = $class_name;
            }
        } else {
            self::$class[$name] = $class;
        }
    }

    /**
     * @param string $name
     * @param array  $vars
     *
     * @return mixed
     * @throws ClassNotExistException
     */
    protected function getInstance($name, $vars = [])
    {
        if (isset(self::$class[$name])) {
            if (!isset(self::$object[$name])) {
                $class  = self::$class[$name];
                $object = new $class($vars);
                self::bindInstance($name, $object);
            }
            return self::$object[$name];
        } else if (isset(self::$object[$name])) {
            return self::$object[$name];
        } else {
            throw new ClassNotExistException('class not exists: ' . $name);
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    protected function hasInstance($name)
    {
        if (isset(self::$class[$name])) {
            return true;
        } else if (isset(self::$object[$name])) {
            return true;
        }
        return false;
    }

    /**
     * @param $name
     * @param $object
     */
    protected function bindInstance($name, $object)
    {
        if (is_array($name)) {
            $object_list = $name;
            foreach ($object_list as $object_name => $obj) {
                if (!isset(self::$class[$object_name])) {
                    self::$class[$object_name] = get_class($obj);
                }
                self::$object[$object_name] = $obj;
            }
        } else {
            if (is_null($object)) {
                throw new \InvalidArgumentException('the $object argument cannot be null');
            }
            if (!isset(self::$class[$name])) {
                self::$class[$name] = get_class($object);
            }
            self::$object[$name] = $object;
        }
    }

    protected function deleteInstance($name)
    {
        if (isset(self::$class[$name])) {
            unset(self::$class[$name]);
        }
        if (isset(self::$object[$name])) {
            unset(self::$object[$name]);
        }
    }

    public function __call($name, $arguments)
    {
        $name = strtolower($name);
        return $this->getInstance($name);
    }

    public static function __callStatic($func, $arguments)
    {
        if (in_array($func, ["set", "get", "bind", "has", "delete"])) {
            $func = $func . "Instance";
            return call_user_func_array([self::instance(), $func], $arguments);
        }
        return null;
    }

    public function offsetExists($key)
    {
        return isset(self::$class[$key]) || isset(self::$object[$key]);
    }

    public function offsetGet($key)
    {
        return $this->getInstance($key);
    }

    public function offsetSet($key, $value)
    {
        $this->setInstance($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->deleteInstance($key);
    }

    public function getIterator()
    {
        return (new \ArrayObject(self::$class))->getIterator();
    }

    public function __debugInfo()
    {
        $data = get_object_vars($this);
        unset($data['object'], $data['class']);

        return $data;
    }
}
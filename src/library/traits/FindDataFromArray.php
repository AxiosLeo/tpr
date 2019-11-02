<?php

declare(strict_types=1);

namespace tpr\library\traits;

trait FindDataFromArray
{
    /**
     * @param $keyArray
     * @param $array
     * @param $default
     *
     * @return mixed
     */
    private function find($keyArray, $array, $default = null)
    {
        if (1 === \count($keyArray)) {
            return isset($array[$keyArray[0]]) ? $array[$keyArray[0]] : $default;
        }
        $key0 = $keyArray[0];
        unset($keyArray[0]);
        $keyArray = array_values($keyArray);
        if (!isset($array[$key0])) {
            return $default;
        }

        return $this->find($keyArray, $array[$key0], $default);
    }
}

<?php

namespace tpr\traits;


trait InstanceTraits
{
    private static $instance;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }
}
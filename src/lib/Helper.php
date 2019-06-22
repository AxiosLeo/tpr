<?php

namespace tpr\lib;

final class Helper
{
    public static function renderString(string $template, array $params): string
    {
        foreach ($params as $name => $value) {
            $template = str_replace('{' . $name . '}', $value, $template);
        }
        return $template;
    }
}
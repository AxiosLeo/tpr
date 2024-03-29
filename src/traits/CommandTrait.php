<?php

declare(strict_types=1);

namespace tpr\traits;

use tpr\Console;

trait CommandTrait
{
    protected function action($action_list, $default = null, $use_argument = true, $use_option = true, $message = 'select an action ')
    {
        $action = null;
        if ($use_argument && Console::input()->hasArgument('action')) {
            $action = Console::input()->getArgument('action');
        }

        if ($use_option && null === $action) {
            $action = Console::input()->getOption('action');
        }

        if (is_numeric($action) && isset($action_list[$action])) {
            $action = $action_list[$action];
        }

        if (empty($action)) {
            if (null === $default) {
                $default = $action_list[0];
            } elseif (is_numeric($default) && isset($action_list[$default])) {
                $default = $action_list[$default];
            }
            $action = Console::output()->choice($message, $action_list, $default);
        }

        return $action;
    }

    protected function highlight($text): string
    {
        return CONSOLE_STYLE_HIGHLIGHT . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function underline($text): string
    {
        return CONSOLE_STYLE_UNDERLINE . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function winkle($text): string
    {
        return CONSOLE_STYLE_WINKLE . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function invert($text): string
    {
        return CONSOLE_STYLE_INVERT . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function blank($text): string
    {
        return CONSOLE_STYLE_BLANK . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function black($text): string
    {
        return CONSOLE_STYLE_BACKGROUND_30 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function red($text): string
    {
        return CONSOLE_STYLE_BACKGROUND_31 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function green($text): string
    {
        return CONSOLE_STYLE_BACKGROUND_32 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function yellow($text): string
    {
        return CONSOLE_STYLE_BACKGROUND_33 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function blue($text): string
    {
        return CONSOLE_STYLE_BACKGROUND_34 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function purple($text): string
    {
        return CONSOLE_STYLE_BACKGROUND_35 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function darkGreen($text): string
    {
        return CONSOLE_STYLE_BACKGROUND_36 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function white($text): string
    {
        return CONSOLE_STYLE_BACKGROUND_37 . $text . CONSOLE_STYLE_DEFAULT;
    }
}

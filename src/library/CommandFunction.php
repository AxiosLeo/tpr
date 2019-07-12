<?php

declare(strict_types=1);

namespace tpr\library;

use tpr\Console;

trait CommandFunction
{
    protected function fixed($str = '', $length = 15, $pad_type = STR_PAD_RIGHT, $filler = ' ')
    {
        return str_pad($str, $length, $filler, $pad_type);
    }

    protected function shell($cmd, $show_command = false)
    {
        while (@ob_end_flush()) {
            continue;
        } // end all output buffers if any

        if ($show_command) {
            Console::output()->writeln($this->green($cmd));
        }

        $proc = popen($cmd, 'r');
        while (!feof($proc)) {
            echo fread($proc, 4096);
            @flush();
        }
    }

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

    protected function highlight($text)
    {
        return CONSOLE_STYLE_HIGHLIGHT . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function underline($text)
    {
        return CONSOLE_STYLE_UNDERLINE . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function winkle($text)
    {
        return CONSOLE_STYLE_WINKLE . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function invert($text)
    {
        return CONSOLE_STYLE_INVERT . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function blank($text)
    {
        return CONSOLE_STYLE_BLANK . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function black($text)
    {
        return CONSOLE_STYLE_BACKGROUND_30 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function red($text)
    {
        return CONSOLE_STYLE_BACKGROUND_31 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function green($text)
    {
        return CONSOLE_STYLE_BACKGROUND_32 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function yellow($text)
    {
        return CONSOLE_STYLE_BACKGROUND_33 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function blue($text)
    {
        return CONSOLE_STYLE_BACKGROUND_34 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function purple($text)
    {
        return CONSOLE_STYLE_BACKGROUND_35 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function darkGreen($text)
    {
        return CONSOLE_STYLE_BACKGROUND_36 . $text . CONSOLE_STYLE_DEFAULT;
    }

    protected function white($text)
    {
        return CONSOLE_STYLE_BACKGROUND_37 . $text . CONSOLE_STYLE_DEFAULT;
    }
}

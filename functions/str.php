<?php

declare (strict_types = 1);

namespace tpr\functions\str;

function fixed(string $str = '', int $length = 15, int $pad_type = \STR_PAD_RIGHT, string $filler = ' '): string
{
    return str_pad($str, $length, $filler, $pad_type);
}

function highlight($text): string
{
    return CONSOLE_STYLE_HIGHLIGHT . $text . CONSOLE_STYLE_DEFAULT;
}

function underline($text): string
{
    return CONSOLE_STYLE_UNDERLINE . $text . CONSOLE_STYLE_DEFAULT;
}

function winkle($text): string
{
    return CONSOLE_STYLE_WINKLE . $text . CONSOLE_STYLE_DEFAULT;
}

function invert($text): string
{
    return CONSOLE_STYLE_INVERT . $text . CONSOLE_STYLE_DEFAULT;
}

function blank($text): string
{
    return CONSOLE_STYLE_BLANK . $text . CONSOLE_STYLE_DEFAULT;
}

function black($text): string
{
    return CONSOLE_STYLE_BACKGROUND_30 . $text . CONSOLE_STYLE_DEFAULT;
}

function red($text): string
{
    return CONSOLE_STYLE_BACKGROUND_31 . $text . CONSOLE_STYLE_DEFAULT;
}

function green($text): string
{
    return CONSOLE_STYLE_BACKGROUND_32 . $text . CONSOLE_STYLE_DEFAULT;
}

function yellow($text): string
{
    return CONSOLE_STYLE_BACKGROUND_33 . $text . CONSOLE_STYLE_DEFAULT;
}

function blue($text): string
{
    return CONSOLE_STYLE_BACKGROUND_34 . $text . CONSOLE_STYLE_DEFAULT;
}

function purple($text): string
{
    return CONSOLE_STYLE_BACKGROUND_35 . $text . CONSOLE_STYLE_DEFAULT;
}

function darkGreen($text): string
{
    return CONSOLE_STYLE_BACKGROUND_36 . $text . CONSOLE_STYLE_DEFAULT;
}

function white($text): string
{
    return CONSOLE_STYLE_BACKGROUND_37 . $text . CONSOLE_STYLE_DEFAULT;
}
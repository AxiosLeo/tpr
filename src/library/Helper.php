<?php

namespace tpr\library;

final class Helper
{
    public static function renderString(string $template, array $params): string
    {
        foreach ($params as $name => $value) {
            $template = str_replace('{' . $name . '}', $value, $template);
        }

        return $template;
    }

    public static function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);

        return json_decode(
            json_encode(
                simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
            ),
            true
        );
    }

    public static function nonce($salt)
    {
        return md5($salt . uniqid(md5(microtime(true)), true));
    }
}

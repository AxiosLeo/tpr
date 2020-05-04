<?php

declare(strict_types=1);

namespace tpr\library;

use tpr\App;

final class Helper
{
    public static function renderString(string $template, array $params): string
    {
        foreach ($params as $name => $value) {
            $template = str_replace('{' . $name . '}', $value, $template);
        }

        return $template;
    }

    public static function xmlToArray(string $xml)
    {
        libxml_disable_entity_loader(true);

        return json_decode(
            json_encode(
                simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
            ),
            true
        );
    }

    public static function nonce(string $salt)
    {
        return md5($salt . uniqid(md5(microtime(true)), true));
    }

    public static function tmp($tmp_file, $tmp_data = null)
    {
        $tmp_file .= '.php';
        if (null === $tmp_data) {
            if (true === App::debugMode() || !\tpr\Files::exist($tmp_file)) {
                return null;
            }

            return require $tmp_file;
        }
        if (!App::debugMode()) {
            file_put_contents($tmp_file, "<?php\nreturn " . var_export($tmp_data, true) . ";\n");
        }
        unset($tmp_file);

        return $tmp_data;
    }
}

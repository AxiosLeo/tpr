<?php

declare(strict_types=1);

namespace tpr\core\response;

class Xml extends ResponseAbstract
{
    public string $content_type = 'text/xml';

    public function output($result = null): string
    {
        if (null === $result) {
            $result = [];
        }

        return $this->xmlEncode($result);
    }

    protected function xmlEncode(array $data): string
    {
        $attr = '';
        if (!empty($this->options->root_attr)) {
            $array = [];
            foreach ($this->options->root_attr as $key => $value) {
                $array[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $array);
        }
        $attr = empty($attr) ? '' : " {trim({$attr})}";
        $xml  = "<?xml version=\"1.0\" encoding=\"{$this->options->encoding}\"?>";
        $xml .= "<{$this->options->root_node}{$attr}>";
        $xml .= $this->dataToXml($data, $this->options->item_node, $this->options->item_key);
        $xml .= "</{$this->options->root_node}>";

        return $xml;
    }

    /**
     * convert array to xml string.
     *
     * @param array  $data array data
     * @param string $item <item></item>
     * @param string $id   <item id=""></item>
     */
    protected function dataToXml(array $data, string $item, string $id): string
    {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $id && $attr = " {$id}=\"{$key}\"";
                $key         = $item;
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= (\is_array($val) || \is_object($val)) ? $this->dataToXml($val, $item, $id) : $val;
            $xml .= "</{$key}>";
        }

        return $xml;
    }
}

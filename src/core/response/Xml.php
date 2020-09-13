<?php

declare(strict_types=1);

namespace tpr\core\response;

class Xml extends ResponseAbstract
{
    public string $content_type = 'text/xml';

    /**
     * 处理数据.
     *
     * @param mixed $data 要处理的数据
     */
    public function output($data = null): string
    {
        // XML数据转换
        return $this->xmlEncode($data);
    }

    /**
     * XML编码
     *
     * @param mixed  $data
     * @param string $root     根节点名
     * @param string $item     数字索引的子节点名
     * @param string $attr     根节点属性
     * @param string $id       数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     */
    protected function xmlEncode(string $data): string
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
     * 数据XML编码
     *
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id   数字索引key转换为的属性名
     */
    protected function dataToXml($data, string $item, string $id): string
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

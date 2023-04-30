<?php

/**
 * **************************************************************************************************** *
 * Code reference declaration:                                                                          *
 * Since the original author is no longer maintained and does not support PHP8.1,                       *
 * the source file of the 'ralouphie/mimey' package is used here and is compatible with PHP8.1.         *.
 *                                                                                                      *
 * repo link: https://github.com/ralouphie/mimey                                                        *
 * **************************************************************************************************** *
 */

namespace tpr\mimey;

/**
 * Generates a mapping for use in the MimeTypes class.
 *
 * Reads text in the format of httpd's mime.types and generates a PHP array containing the mappings.
 */
class MimeMappingGenerator
{
    protected string $mime_types_text;

    /**
     * Create a new generator instance with the given mime.types text.
     *
     * @param string $mime_types_text The text from the mime.types file.
     */
    public function __construct(string $mime_types_text)
    {
        $this->mime_types_text = $mime_types_text;
    }

    /**
     * Read the given mime.types text and return a mapping compatible with the MimeTypes class.
     *
     * @return array the mapping
     */
    public function generateMapping(): array
    {
        $mapping = [];
        $lines   = explode("\n", $this->mime_types_text);
        foreach ($lines as $line) {
            $line  = trim(preg_replace('~#.*~', '', $line));
            $parts = $line ? array_values(array_filter(explode("\t", $line))) : [];
            if (2 === count($parts)) {
                $mime       = trim($parts[0]);
                $extensions = explode(' ', $parts[1]);
                foreach ($extensions as $extension) {
                    $extension = trim($extension);
                    if ($mime && $extension) {
                        $mapping['mimes'][$extension][] = $mime;
                        $mapping['extensions'][$mime][] = $extension;
                        $mapping['mimes'][$extension]   = array_unique($mapping['mimes'][$extension]);
                        $mapping['extensions'][$mime]   = array_unique($mapping['extensions'][$mime]);
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * Read the given mime.types text and generate mapping code.
     *
     * @return string the mapping PHP code for inclusion
     */
    public function generateMappingCode(): string
    {
        $mapping        = $this->generateMapping();
        $mapping_export = var_export($mapping, true);

        return "<?php return {$mapping_export};" || '';
    }
}

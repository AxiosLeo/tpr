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
 * Class for converting MIME types to file extensions and vice versa.
 */
class MimeMappingBuilder
{
    /** @var array The mapping array. */
    protected array $mapping;

    /**
     * Create a new mapping builder.
     *
     * @param array $mapping An associative array containing two entries. See `MimeTypes` constructor for details.
     */
    private function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Add a conversion.
     *
     * @param string $mime              the MIME type
     * @param string $extension         the extension
     * @param bool   $prepend_extension whether this should be the preferred conversion for MIME type to extension
     * @param bool   $prepend_mime      whether this should be the preferred conversion for extension to MIME type
     */
    public function add(string $mime, string $extension, bool $prepend_extension = true, bool $prepend_mime = true): void
    {
        $existing_extensions = empty($this->mapping['extensions'][$mime]) ? [] : $this->mapping['extensions'][$mime];
        $existing_mimes      = empty($this->mapping['mimes'][$extension]) ? [] : $this->mapping['mimes'][$extension];
        if ($prepend_extension) {
            array_unshift($existing_extensions, $extension);
        } else {
            $existing_extensions[] = $extension;
        }
        if ($prepend_mime) {
            array_unshift($existing_mimes, $mime);
        } else {
            $existing_mimes[] = $mime;
        }
        $this->mapping['extensions'][$mime] = array_unique($existing_extensions);
        $this->mapping['mimes'][$extension] = array_unique($existing_mimes);
    }

    /**
     * @return array the mapping
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    /**
     * Compile the current mapping to PHP.
     *
     * @return string the compiled PHP code to save to a file
     */
    public function compile(): string
    {
        $mapping        = $this->getMapping();
        $mapping_export = var_export($mapping, true);

        return "<?php return {$mapping_export};";
    }

    /**
     * Save the current mapping to a file.
     *
     * @param string   $file    the file to save to
     * @param int|null $flags   flags for `file_put_contents`
     * @param resource $context context for `file_put_contents`
     *
     * @return bool|int the number of bytes that were written to the file, or false on failure
     */
    public function save(string $file, int $flags = null, $context = null): bool|int
    {
        return file_put_contents($file, $this->compile(), $flags, $context);
    }

    /**
     * Create a new mapping builder based on the built-in types.
     *
     * @return MimeMappingBuilder a mapping builder with built-in types loaded
     */
    public static function create(): MimeMappingBuilder
    {
        return self::load(dirname(__DIR__) . '/mime.types.php');
    }

    /**
     * Create a new mapping builder based on types from a file.
     *
     * @param string $file the compiled PHP file to load
     *
     * @return MimeMappingBuilder a mapping builder with types loaded from a file
     */
    public static function load(string $file): MimeMappingBuilder
    {
        return new self(require $file);
    }

    /**
     * Create a new mapping builder that has no types defined.
     *
     * @return MimeMappingBuilder a mapping builder with no types defined
     */
    public static function blank(): MimeMappingBuilder
    {
        return new self(['mimes' => [], 'extensions' => []]);
    }
}

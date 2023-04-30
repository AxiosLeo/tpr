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
class MimeTypes implements MimeTypesInterface
{
    /** @var array The mapping array. */
    protected array $mapping;

    /** @var array|null The cached built-in mapping array. */
    private static ?array $built_in = null;

    /**
     * Create a new mime types instance with the given mappings.
     *
     * If no mappings are defined, they will default to the ones included with this package.
     *
     * @param array|null $mapping An associative array containing two entries.
     *                            Entry "mimes" being an associative array of extension to array of MIME types.
     *                            Entry "extensions" being an associative array of MIME type to array of extensions.
     *                            Example:
     *                            <code>
     *                            array(
     *                            'extensions' => array(
     *                            'application/json' => array('json'),
     *                            'image/jpeg'       => array('jpg', 'jpeg'),
     *                            ...
     *                            ),
     *                            'mimes' => array(
     *                            'json' => array('application/json'),
     *                            'jpeg' => array('image/jpeg'),
     *                            ...
     *                            )
     *                            )
     *                            </code>
     */
    public function __construct(array $mapping = null)
    {
        if (null === $mapping) {
            $this->mapping = self::getBuiltIn();
        } else {
            $this->mapping = $mapping;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType(string $extension): ?string
    {
        $extension = $this->cleanInput($extension);
        if (!empty($this->mapping['mimes'][$extension])) {
            return $this->mapping['mimes'][$extension][0];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(string $mime_type): ?string
    {
        $mime_type = $this->cleanInput($mime_type);
        if (!empty($this->mapping['extensions'][$mime_type])) {
            return $this->mapping['extensions'][$mime_type][0];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllMimeTypes(string $extension): array
    {
        $extension = $this->cleanInput($extension);
        if (isset($this->mapping['mimes'][$extension])) {
            return $this->mapping['mimes'][$extension];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllExtensions(string $mime_type): array
    {
        $mime_type = $this->cleanInput($mime_type);
        if (isset($this->mapping['extensions'][$mime_type])) {
            return $this->mapping['extensions'][$mime_type];
        }

        return [];
    }

    /**
     * Get the built-in mapping.
     *
     * @return array the built-in mapping
     */
    protected static function getBuiltIn(): array
    {
        if (null === self::$built_in) {
            self::$built_in = require __DIR__ . DIRECTORY_SEPARATOR . 'mime.types.php';
        }

        return self::$built_in;
    }

    /**
     * Normalize the input string using lowercase/trim.
     *
     * @param string $input the string to normalize
     *
     * @return string the normalized string
     */
    private function cleanInput(string $input): string
    {
        return strtolower(trim($input));
    }
}

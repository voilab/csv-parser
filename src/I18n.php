<?php
namespace voilab\csv;

class I18n implements I18nInterface {

    /**
     * Translated texts
     * @var array
     */
    private $text = [
        'NOFILE' => "File [%s] doesn't exist",
        'NORESOURCE' => "CSV data must be a resource",
        'NOCOLUMN' => "No column configured in options",
        'DIFFCOLUMNS' => "At line [%s], columns don't match headers",
        'HEADERMISSING' => "Header [%s] not found in CSV resource",
        'EMPTY' => "CSV data is empty",
        'HEADEREXISTS' => "Header [%s] can't be the same for two columns",
        'NOTSEEKABLE' => "Resource not seekable. Please add a header line or make it seekable"
    ];

    /**
     * Constructor for translations
     *
     * @param array $text translations indexed by Exception constant names
     */
    public function __construct(array $text = [])
    {
        if (count($text)) {
            $this->text = $text;
        }
    }

    /**
     * @inheritDoc
     */
    public function t(string $key) : string
    {
        return isset($this->text[$key]) ? $this->text[$key] : $key;
    }
}

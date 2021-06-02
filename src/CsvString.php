<?php
namespace voilab\csv;

class CsvString extends CsvResource
{
    /**
     * String resource stream constructor
     *
     * @param string $str the CSV string
     * @param array $options the options array
     */
    public function __construct(string $str, array $options = [])
    {
        if ($options['autoDetectLn'] !== null) {
            // must be set before fopen is called
            ini_set('auto_detect_line_endings', (bool) $options['autoDetectLn']);
        }
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $str);
        rewind($resource);
        parent::__construct($resource, $options);
    }
}

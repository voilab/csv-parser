<?php
namespace voilab\csv;

class CsvFile extends CsvResource
{
    /**
     * File resource stream constructor
     *
     * @param string $file Path and name of the file
     * @param array $options the options array
     */
    public function __construct(string $file, array $options = [])
    {
        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf("File [%s] doesn't exist", $file));
        }
        if ($options['autoDetectLn'] !== null) {
            // must be set before fopen is called
            ini_set('auto_detect_line_endings', (bool) $options['autoDetectLn']);
        }
        $resource = fopen($file, 'r');
        parent::__construct($resource, $options);
    }
}

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
        $resource = fopen($file, 'r');
        parent::__construct($resource, $options);
    }
}

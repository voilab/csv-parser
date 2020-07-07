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
    public function __construct($str, array $options = [])
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $str);
        rewind($resource);
        parent::__construct($resource, $options);
    }
}

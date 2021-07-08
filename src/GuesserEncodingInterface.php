<?php
namespace voilab\csv;

interface GuesserEncodingInterface
{

    /**
     * Try to find how content is encoded and encode it in the desired encoding
     *
     * @param string $value CSV cell content (before parsing). Should be a string
     * @param array $row Full CSV row (before parsing)
     * @param int $index Line index. Correspond to the line number in the CSV resource (taken headers into account)
     * @param array $meta Current column information
     * @param array $parserOptions Configuration options for parsing
     * @return string The encoded string
     */
    public function encode($value, array $row, int $index, array $meta, array $parserOptions) : string;
}

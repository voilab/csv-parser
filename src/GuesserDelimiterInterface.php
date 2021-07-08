<?php
namespace voilab\csv;

interface GuesserDelimiterInterface
{
    /**
     * Returns the best delimiter. Should throw an exception if delimiter is
     * not found or if the result is too ambiguous
     *
     * @param CsvInterface $data The CSV data object
     * @param array $parserOptions Configuration options for parsing
     * @return string The guessed delimiter
     * @throws \Exception If no delimiter is found or too ambiguous
     */
    public function guess(CsvInterface $data, array $parserOptions) : string;
}

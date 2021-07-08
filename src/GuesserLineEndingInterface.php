<?php
namespace voilab\csv;

interface GuesserLineEndingInterface
{
    /**
     * Try to find the best line ending char, by parsing some rows
     *
     * @param CsvInterface $data The CSV data object
     * @param array $parserOptions Configuration options for parsing
     * @return string|null The guessed line ending. If null, default line ending is used
     */
    public function guess(CsvInterface $data, array $parserOptions) : ?string;
}

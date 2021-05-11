<?php
namespace voilab\csv;

interface GuesserInterface
{
    /**
     * Try to find the best delimiter, by parsing some rows. Line ending
     * guessing is already done at this stage, so it's unnecessary to set
     * this option here.
     *
     * @param CsvInterface $data The CSV data object
     * @return string The guessed delimiter
     * @throws \Exception If no delimiter is found or too ambiguous
     */
    public function guessDelimiter(CsvInterface $data) : string;

    /**
     * Try to find the best line ending char, by parsing some rows
     *
     * @param CsvInterface $data The CSV data object
     * @return string|null The guessed line ending. If null, default line ending is used
     */
    public function guessLineEnding(CsvInterface $data) : ?string;

    /**
     * Try to find how content is encoded and encode it in the desired encoding
     *
     * @param string $value CSV cell content (before parsing). Should be a string
     * @param int $index Line index. Correspond to the line number in the CSV resource (taken headers into account)
     * @param array $meta Current column information
     * @param array $parserOptions Configuration options for parsing
     * @return string The encoded string
     */
    public function guessEncoding($value, int $index, array $meta, array $parserOptions) : string;
}

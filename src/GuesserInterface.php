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
     * @param array $parserOptions Parser options
     * @return string The guessed delimiter
     * @throws Exception If no delimiter is found or too ambiguous
     */
    public function guessDelimiter(CsvInterface $data, array $parserOptions) : string;

    /**
     * Try to find the best line ending char, by parsing some rows
     *
     * @param CsvInterface $data The CSV data object
     * @return string|null The guessed line ending. If null, default line ending is used
     */
    public function guessLineEnding(CsvInterface $data) : ?string;
}

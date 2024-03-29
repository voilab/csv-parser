<?php
namespace voilab\csv;

use Psr\Http\Message\StreamInterface;

interface CsvInterface extends StreamInterface
{
    /**
     * Gets line from file pointer and parse for CSV fields
     *
     * @see https://www.php.net/fgetcsv
     * @param int $length
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * @return null|bool|array NULL if an invalid handle is supplied or FALSE on other errors, including end of file.
     */
    public function getCsv($length, $delimiter, $enclosure, $escape);

    /**
     * Set an option metadata configured in this resource
     *
     * @param string $key The metadata key
     * @param mixed $value The value
     * @return void
     */
    public function setMetadata(string $key, $value);

    /**
     * Returns the underlying resource
     *
     * @return mixed The underlying resource
     */
    public function getResource();
}

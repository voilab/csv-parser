<?php
namespace voilab\csv;

interface OptimizerInterface
{
    /**
     * Take the value extracted from CSV resource. Can return any mixed value
     *
     * @param mixed $value CSV cell content (after parsing)
     * @param int $index Line index. Correspond to the line number in the CSV resource (taken headers into account)
     * @param array $row Entire row data, raw from fgetcsv. These datas are not the result of the columns functions
     * @param array $parsed Parsed data from previous columns (columns are handled one after the other)
     * @param array $meta Current column information
     * @param array $options configuration options for parsing
     * @return ?mixed any mixed value
     */
    public function parse($value, int $index, array $row, array $parsed, array $meta, array $options);

    /**
     * Take all parsed data from one column. Must return an array indexed by
     * these values.
     *
     * For example:
     * If data = ['a', 'b', 'c'] and these are IDs for objects of type User,
     * the result must be: ['a' => User, 'b' => User, 'c' => User]
     *
     * @param array $data the set of data
     * @param array $meta column metadata
     * @param array $options configuration options for parsing
     * @return array indexed array
     */
    public function reduce(array $data, array $meta, array $options) : array;

    /**
     * Called when a value in CSV resource doesn't exist in the reduced data
     * from the reduce function
     *
     * @param mixed $value CSV cell content (after parsing)
     * @param array $meta column metadata
     * @param array $options configuration options for parsing
     * @return mixed
     */
    public function absent($value, array $meta, array $options);
}

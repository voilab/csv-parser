<?php
namespace voilab\csv;

class Optimizer implements OptimizerInterface
{
    /**
     * Function used to parse data from CSV column
     * @var callable
     */
    private $parseFn;

    /**
     * Function used to create a new array of data for a column
     * @var callable
     */
    private $reduceFn;

    /**
     * Constructor
     *
     * @param callable $parseFn Function used to parse data from CSV column
     * @param callable $reduceFn
     */
    public function __construct(callable $parseFn, callable $reduceFn)
    {
        $this->parseFn = $parseFn;
        $this->reduceFn = $reduceFn;
    }

    /**
     * @inheritDoc
     */
    public function parse($value, $index, array $row, array $parsed, array $meta, array $options)
    {
        $key = $this->parseFn;
        return $key($value, $index, $row, $parsed, $meta, $options);
    }

    /**
     * @inheritDoc
     */
    public function reduce(array $data, array $meta, array $options)
    {
        $key = $this->reduceFn;
        return $key($data, $meta, $options);
    }
}

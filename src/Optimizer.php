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
     * Function used to do something with values that are absent from reduce
     * @var callable
     */
    private $absentFn;

    /**
     * Constructor
     *
     * @param callable $parseFn Function used to parse data from CSV column
     * @param callable $reduceFn
     * @param callable|null $absentFn
     */
    public function __construct(callable $parseFn, callable $reduceFn, callable $absentFn = null)
    {
        $this->parseFn = $parseFn;
        $this->reduceFn = $reduceFn;
        $this->absentFn = $absentFn;
    }

    /**
     * @inheritDoc
     */
    public function parse($value, int $index, array $row, array $parsed, array $meta, array $options)
    {
        $key = $this->parseFn;
        return $key($value, $index, $row, $parsed, $meta, $options);
    }

    /**
     * @inheritDoc
     */
    public function reduce(array $data, array $meta, array $options) : array
    {
        $key = $this->reduceFn;
        return $key($data, $meta, $options);
    }

    /**
     * @inheritDoc
     */
    public function absent($value, int $index, array $meta, array $options)
    {
        $key = $this->absentFn;
        return $key ? $key($value, $index, $meta, $options) : $value;
    }
}

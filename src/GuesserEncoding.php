<?php
namespace voilab\csv;

class GuesserEncoding implements GuesserEncodingInterface
{
    /**
     * Options array
     * @var array
     */
    protected $options = [
        'to' => 'utf-8',
        'from'=> null,
        'encodings' => null,
        'strict' => false
    ];

    /**
     * Index used to detect encoding only once per row
     * @var int
     */
    protected $index;

    /**
     * Guesser constructor
     *
     * @param array $options the guess options array
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        $this->options['to'] = strtolower($this->options['to']);
        if ($this->options['from']) {
            $this->options['from'] = strtolower($this->options['from']);
        }

        $this->options['encodings'] = array_map('strtolower', $this->options['encodings'] ?: mb_list_encodings());

        if (
            !in_array($this->options['to'], $this->options['encodings'])
            || ($this->options['from'] && !in_array($this->options['from'], $this->options['encodings']))
        ) {
            throw new \OutOfBoundsException(sprintf("Encoding [%s] is not supported", $this->options['to']));
        }
    }

    /**
     * @inheritDoc
     */
    public function encode($data, array $row, int $index, array $meta, array $parserOptions) : string
    {
        if (!trim($data)) {
            // skip empty cells
            return $data;
        }
        $from = $this->options['from'];
        if (!$from || $this->index !== $index) {
            // make detection only once per row
            $from = mb_detect_encoding(implode(',', $row), $this->options['encodings'], $this->options['strict']);
            if ($from === false) {
                throw new \RuntimeException(sprintf("Unable to detect encoding at line [%s]", $index));
            }
        }
        $this->index = $index;
        return mb_convert_encoding((string) $data, $this->options['to'], $from);
    }
}

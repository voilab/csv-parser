<?php
namespace voilab\csv;

class Guesser implements GuesserInterface
{
    /**
     * Options array
     * @var array
     */
    protected $options = [
        'length' => 1024 * 1024,
        'enclosure' => '"',
        'delimiters' => [',', ';', ':', "\t", '|', ' '],
        'encodingTo' => 'utf8',
        'size' => 10,
        'debug' => false
    ];

    protected $encodingList;

    /**
     * Guesser constructor
     *
     * @param array $options the guess options array
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->options['encodingTo'] = strtolower($this->options['encodingTo']);
    }

    public function guessDelimiter(CsvInterface $data) : string
    {
        if (is_iterable($data->getResource())) {
            // no delimiter needed for iterable resources
            return '';
        }
        $result = [];
        foreach ($this->options['delimiters'] as $delimiter) {
            $result[$delimiter] = $this->getDelimiterData($data, $delimiter);
        }
        return $this->getDelimiter($result, $data);
    }

    public function guessLineEnding(CsvInterface $data) : ?string
    {
        if (is_iterable($data->getResource())) {
            return null;
        }
        $input = $this->cleanInput($this->getInput($data));
        return $this->getLineEnding($data, $input);
    }

    public function guessEncoding($data, int $index, array $meta, array $parserOptions) : string
    {
        if (!$this->encodingList) {
            $list = mb_list_encodings();
            $this->encodingList = array_combine(array_map('strtolower', $list), $list);
            if (!in_array($this->options['encodingTo'], $this->encodingList)) {
                throw new \OutOfBoundException(sprintf("Encoding [%s] is not found in mb_list_encodings", $this->options['encodingTo']));
            }
        }
        return mb_convert_encoding((string) $data, $this->options['encodingTo'], 'utf-8');
    }

    /**
     * Returns the best delimiter. Should throw an exception if delimiter is
     * not found or if the result is too ambiguous
     *
     * @see guessDelimiter()
     * @param CsvInterface $data The CSV data object
     * @param array $results Result data for each tested delimiter
     * @return string The guessed delimiter
     * @throws \Exception If no delimiter is found or too ambiguous
     */
    protected function getDelimiter(CsvInterface $data, array $results) : string
    {

    }

    /**
     * Extract information about what this delimiter has done
     *
     * @see guessDelimiter()
     * @param CsvInterface $data The CSV data object
     * @param string $delimiter The delimiter to test
     * @return array Delimiter information
     */
    protected function getDelimiterData(CsvInterface $data, string $delimiter) : array
    {
        $lines = $this->getLines($data, $delimiter);
        $avg = [];
        $expected = 0;
        foreach ($lines as $line) {
            if (!$expected) {
                $expected = count($line);
            }
            $avg[] = count($line);
        }
        $avg = array_sum($avg) / count($avg);
        return [
            'delimiter' => $delimiter,
            'lines' => count($lines),
            'expectedFields' => $expected,
            'averageFields' => $avg,
            'columnsRatio' => $avg / count($data->getOption('columns'))
        ];
    }

    /**
     * Reset parser for this new delimiter
     *
     * @see guessDelimiter()
     * @param CsvInterface $data The CSV data object
     * @param string $delimiter The current delimiter to check
     * @return array
     */
    protected function getLines(CsvInterface $data, string $delimiter) : array
    {
        $data->rewind();
        $o = $data->getOption();
        $lines = [];
        for ($i = 0; $i < $o['size']; $i += 1) {
            $line = $data->getCsv($o['length'], $delimiter, $o['enclosure'], $o['escape']);
            if (!$line) {
                break;
            }
            $lines[] = $line;
        }
        return $lines;
    }

    /**
     * Returns the best line ending
     *
     * @see guessLineEnding()
     * @param CsvInterface $data The CSV data object
     * @param string $input The filtered data from the CSV data object
     * @return string|null The guessed line ending. If null, default line ending is used
     */
    protected function getLineEnding(CsvInterface $data, string $input) : ?string
    {
        $r = explode("\r", $input);
        $n = explode("\n", $input);

        if (count($r) === 1 || (count($n) > 1 && strlen($n[0]) < strlen($r[0]))) {
            return "\n";
        }
        $both = 0;
        for ($i = 0; $i < count($r); $i += 1) {
            if ($r[$i][0] === "\n") {
                $both += 1;
            }
        }
        return $both < count($r) / 2 ? "\n" : "\r\n";
    }

    /**
     * Get some content from the CSV data
     *
     * @see guessLineEnding()
     * @param CsvInterface $data The CSV data object
     * @return string Some content
     */
    protected function getInput(CsvInterface $data) : string
    {
        $data->rewind();
        return $data->read($this->options['length']);
    }

    /**
     * Try to remove new lines inside enclosure
     *
     * @see guessLineEnding()
     * @param CsvInterface $data The CSV data object
     * @return string cleaned content
     */
    protected function cleanInput(string $input) : string
    {
        if (!$this->options['enclosure']) {
            return $input;
        }
        // encode enclosure for regexp
        $e = preg_quote($this->options['enclosure']);
        // remove all content inside enclosure, so new lines in there will not
        // be taken into account
        return preg_replace('/' . $e . '([^]]*?)' . $e . '/', '', $input);
    }
}

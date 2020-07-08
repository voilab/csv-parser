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
        'size' => 10,
        'debug' => false
    ];

    /**
     * Guesser constructor
     *
     * @param array $options the options array
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function guessDelimiter(CsvInterface $data, array $parserOptions) : string
    {
        if (is_iterable($data->getResource())) {
            return '';
        }
        $result = [];
        foreach ($this->options['delimiters'] as $delimiter) {
            $result[$delimiter] = $this->getDelimiterData($data, $delimiter, $parserOptions);
        }
        print_r($result);
    }

    public function guessLineEnding(CsvInterface $data) : string
    {
        if (is_iterable($data->getResource())) {
            return null;
        }
        $input = $this->cleanInput($this->getInput($data));
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
     * Extract information about what this delimiter has done
     *
     * @param CsvInterface $data The CSV data object
     * @param string $delimiter The delimiter to test
     * @param array $parserOptions Parser options
     * @return array Delimiter information
     */
    protected function getDelimiterData(CsvInterface $data, string $delimiter, array $parserOptions) : array
    {
        $lines = $this->getLines($data, $delimiter, $parserOptions);
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
            'columnsRatio' => $avg / count($parserOptions['columns'])
        ];
    }

    /**
     * Reset parser for this new delimiter
     *
     * @param CsvInterface $data The CSV data object
     * @param string $delimiter The current delimiter to check
     * @param array $parserOptions Parser options
     * @return void
     */
    protected function getLines(CsvInterface $data, string $delimiter, array $parserOptions) : array
    {
        $data->rewind();
        $o = $parserOptions;
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
     * Get some content from the CSV data
     *
     * @param CsvInterface $data The CSV data object
     * @return string Some content
     */
    protected function getInput(CsvInterface $data) : string
    {
        return $data->read($this->options['length']);
    }

    /**
     * Try to remove new lines inside enclosure
     *
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

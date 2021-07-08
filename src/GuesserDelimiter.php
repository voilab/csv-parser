<?php
namespace voilab\csv;

class GuesserDelimiter implements GuesserDelimiterInterface
{
    /**
     * Options array
     * @var array
     */
    protected $options = [
        'delimiters' => [',', ';', ':', "\t", '|', ' '],
        'throwAmbiguous' => true,
        'scoreLimit' => 5,
        'size' => 10
    ];

    /**
     * Guesser constructor
     *
     * @param array $options the guess options array
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * @inheritDoc
     */
    public function guess(CsvInterface $data, array $parserOptions) : string
    {
        $result = [];
        foreach ($this->options['delimiters'] as $delimiter) {
            $result[] = $this->getDelimiterData($data, $delimiter, $parserOptions);
        }
        usort($result, function ($a, $b) {
            return $a['score'] > $b['score'];
        });
        return $this->getDelimiter($result);
    }

    /**
     * Returns the best delimiter based on results of given delimiters
     *
     * @param array $results Result data for each tested delimiter
     * @return string The guessed delimiter
     * @throws \Exception If no delimiter is found or too ambiguous
     */
    protected function getDelimiter(array $results) : string
    {
        $result = array_filter($results, function ($r) {
            return $r['score'] > $this->options['scoreLimit'];
        });
        $delimiters = array_map(function ($r) {
            return $r['delimiter'];
        }, $result);

        // test if all is ok
        if (count($delimiters) === 0) {
            throw new \OutOfBoundsException('No delimiter found!');
        }
        if (count($delimiters) > 1 && $this->options['throwAmbiguous']) {
            throw new \OutOfBoundsException(sprintf('Ambiguous delimiters: found [%s] eligible!', implode(', ', $delimiters)));
        }

        // return best delimiter
        reset($delimiters);
        return current($delimiters);
    }

    /**
     * Extract information about what this delimiter has done, and
     * set a score for it. The higher the score, the most possible
     * delimiter it is.
     *
     * @param CsvInterface $data The CSV data object
     * @param string $delimiter The delimiter to test
     * @param array $parserOptions Configuration options for parsing
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

        $result = [
            'delimiter' => $delimiter,
            'lines' => count($lines),
            'expectedFields' => $expected,
            'averageFields' => $avg,
            'columns' => count($parserOptions['columns']),
            'columnsRatio' => $avg / count($parserOptions['columns'])
        ];
        $result['score'] = $this->getScore($result);
        return $result;
    }

    /**
     * Set a score depending on the results found for this delimiter.
     * The higher the score is, the more eligible the delimiter
     *
     * @param array $result The delimiter result array
     * @return int The score
     */
    protected function getScore(array $result) {
        $score = 1;
        if ($result['columns'] === $result['expectedFields']) {
            $score *= 10;
        }
        if ($result['averageFields'] === $result['expectedFields']) {
            $score *= 5;
        }
        if ($result['columnsRatio'] === 1) {
            $score *= 3;
        }
        return $score;
    }

    /**
     * Reset parser for this new delimiter
     *
     * @param CsvInterface $data The CSV data object
     * @param string $delimiter The current delimiter to check
     * @param array $parserOptions Configuration options for parsing
     * @return array
     */
    protected function getLines(CsvInterface $data, string $delimiter, array $parserOptions) : array
    {
        $data->rewind();
        $o = $parserOptions;
        $lines = [];
        for ($i = 0; $i < $this->options['size']; $i += 1) {
            $line = $data->getCsv($o['length'], $delimiter, $o['enclosure'], $o['escape']);
            if (!$line) {
                break;
            }
            $lines[] = $line;
        }
        return $lines;
    }
}

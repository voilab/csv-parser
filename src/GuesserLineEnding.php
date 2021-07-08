<?php
namespace voilab\csv;

class GuesserLineEnding implements GuesserLineEndingInterface
{
    /**
     * Options array
     * @var array
     */
    protected $options = [
        'length' => 1024 * 1024
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
    public function guess(CsvInterface $data, array $parserOptions) : ?string
    {
        $data->rewind();
        $input = $this->cleanInput(
            $data->read($this->options['length']),
            $parserOptions
        );
        return $this->getLineEnding($input);
    }

    /**
     * Returns the best line ending
     *
     * @param string $input The filtered data from the CSV data object
     * @return string|null The guessed line ending. If null, default line ending is used
     */
    protected function getLineEnding(string $input) : ?string
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
     * Try to remove new lines inside enclosure
     *
     * @param string $input Test data string
     * @param array $parserOptions Configuration options for parsing
     * @return string cleaned content
     */
    protected function cleanInput(string $input, array $parserOptions) : string
    {
        if (!$parserOptions['enclosure']) {
            return $input;
        }
        // encode enclosure for regexp
        $e = preg_quote($parserOptions['enclosure']);
        // remove all content inside enclosure, so new lines in there will not
        // be taken into account
        return preg_replace('/' . $e . '([^]]*?)' . $e . '/', '', $input);
    }
}

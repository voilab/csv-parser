<?php
namespace voilab\csv;

class Parser
{
    /**
     * Column alias to be used in columns definitions
     * @var string
     */
    const COLUMNALIAS = ' as ';

    /**
     * Default options used for parsing CSV
     * @var array
     */
    private $options = [
        // fgetcsv
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
        'length' => 0,
        // headers
        'headers' => true,
        'strictHeaders' => true,
        'ignoreMissingHeaders' => false,
        // big files
        'size' => 0,
        'start' => 0,
        // data pre-manipulation
        'autotrim' => true,
        'onBeforeColumnParse' => null,
        // data post-manipulation
        'onRowParsed' => null,
        'onError' => null,
        // column definition
        'columns' => []
    ];

    /**
     * Constructor of the CSV data parser.
     *
     * @param array $options default options for parsing
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set automatic detection for line endings, to deal with Mac line endings
     *
     * @param bool $value set or unset auto detect line endings
     * @return self
     */
    public function autoDetectLineEndings($value = true) : self
    {
        ini_set('auto_detect_line_endings', (bool) $value);
        return $this;
    }

    /**
     * Parse a CSV from a file
     *
     * @param string $file the CSV path and filename
     * @param array $options configuration options for parsing
     * @return array the processed data
     */
    public function fromFile(string $file, array $options = []) : array
    {
        if (!file_exists($file)) {
            throw new Exception("File [$file] doesn't exist", Exception::NOFILE);
        }
        $resource = fopen($file, 'r');
        $result = $this->fromResource($resource, $options);
        fclose($resource);
        return $result;
    }

    /**
     * Parse a CSV from a data string
     *
     * @param string $data the CSV data string
     * @param array $options configuration options for parsing
     * @return array the processed data
     */
    public function fromString(string $data, array $options = []) : array
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $data);
        rewind($stream);
        $result = $this->fromResource($stream, $options);
        fclose($stream);
        return $result;
    }

    /**
     * Parse a CSV data resource
     *
     * @param resource $data the CSV data resource
     * @param array $options configuration options for parsing
     * @return array the processed data
     */
    public function fromResource($data, array $options) : array
    {
        if (!is_resource($data)) {
            throw new Exception("CSV data must be a resource", Exception::NORESOURCE);
        }
        $options = array_merge($this->options, $options);
        if (!count($options['columns'])) {
            throw new Exception("No column configured in options", Exception::NOCOLUMN);
        }
        // there're two ways to handle no-enclosure: same as separator or 0x00
        if (!$options['enclosure']) {
            $options['enclosure'] = 0x00;
        }

        $columns = $this->getColumns($data, $options);

        $parsed = [];
        $i = 0;
        while (
            (!$options['size'] || $i < $options['size'] + $options['start']) &&
            false !== ($row = fgetcsv($data, $options['length'], $options['delimiter'], $options['enclosure'], $options['escape']))
        ) {
            if ($options['size'] && $i < $options['start']) {
                $i++;
                continue;
            }
            // in resource, 1st line is index 1, not zero. And we have to take
            // headers into account moreover
            $index = $i + ($options['headers'] ? 2 : 1);
            try {
                $rowData = $this->getRow($row, $index, $columns, $options);
                if (is_callable($options['onRowParsed'])) {
                    $options['onRowParsed']($rowData, $index, $parsed, $options);
                }
                $parsed[] = $rowData;
            } catch (\Exception $e) {
                if (is_callable($options['onError'])) {
                    $info = [ 'type' => 'row' ];
                    $options['onError']($e, $index, $info, $options);
                } else {
                    throw $e;
                }
            }
            $i++;
        }
        if ($i === 0) {
            throw new Exception("CSV data is empty", Exception::EMPTY);
        }
        return $parsed;
    }

    /**
     * Explode one row and parse each column, calling method if asked
     *
     * @param array $row the parsed row witht fgetcsv
     * @param int $index the row index in the CSV resource
     * @param array $columns the parsed columns
     * @param array $options configuration options for parsing
     * @return array the processed row
     */
    private function getRow(array $row, int $index, array $columns, array $options) : array
    {
        $parsed = [];
        foreach ($row as $i => $col) {
            $colinfo = [ 'type' => 'column' ];
            try {
                if (!isset($columns[$i])) {
                    throw new Exception("At line [$index], columns don't match headers", Exception::DIFFCOLUMNS);
                }
                $colinfo = array_merge($colinfo, $columns[$i]);
                if ($options['ignoreMissingHeaders'] && $colinfo['full'] === null) {
                    continue;
                }

                $col = $options['autotrim'] ? trim($col) : $col;
                if (is_callable($options['onBeforeColumnParse'])) {
                    $col = $options['onBeforeColumnParse']($col, $index, $colinfo, $options);
                }

                $method = isset($options['columns'][$colinfo['full']])
                    ? $options['columns'][$colinfo['full']]
                    : null;

                $parsed[$colinfo['name']] = $method
                    ? $method($col, $index, $row, $parsed, $options)
                    : $col;

            } catch (\Exception $e) {
                if (is_callable($options['onError'])) {
                    // user will decide what to do with the error
                    $options['onError']($e, $index, $colinfo, $options);
                } else {
                    throw $e;
                }
            }
        }
        return $parsed;
    }

    /**
     * Return the columns
     *
     * @param resource $data the CSV data resource
     * @param array $options configuration options for parsing
     * @return array the columns. If they are aliased, return the aliased ones
     */
    private function getColumns($data, array $options) : array
    {
        $csvHeaders = $this->getCsvHeaders($data, $options);
        $optionsHeaders = $this->getOptionsHeaders($options);

        $headers = [];
        foreach ($csvHeaders as $i => $csvHeader) {
            if (isset($optionsHeaders[$csvHeader])) {
                $headers[$i] = $optionsHeaders[$csvHeader];
            } else {
                if ($options['strictHeaders'] && !$options['ignoreMissingHeaders']) {
                    throw new Exception("Header [$csvHeader] not found in column configuration", Exception::HEADERMISSING);
                }
                // add header with a raw index, since it is not configured in
                // the options array and we authorize it
                $headers[$i] = [
                    'name' => $csvHeader,
                    'csv' => $i,
                    'full' => null
                ];
            }
        }
        return $headers;
    }

    /**
     * Get headers from CSV resource
     *
     * @param resource $data the CSV data resource
     * @param array $options configuration options for parsing
     * @return array
     */
    private function getCsvHeaders($data, array $options) : array
    {
        $columns = fgetcsv($data, $options['length'], $options['delimiter'], $options['enclosure'], $options['escape']);
        if (!$options['headers']) {
            rewind($data);
        }
        if (!$columns || (count($columns) === 1 && $columns[0] === null)) {
            throw new Exception("CSV data is empty", Exception::EMPTY);
        }
        return array_map(
            'trim',
            $options['headers'] ? $columns : array_keys($columns)
        );
    }

    /**
     * Get headers from columns configuration options
     *
     * @param array $options configuration options for parsing
     * @return array
     */
    private function getOptionsHeaders(array $options) : array
    {
        $aliased = [];
        foreach (array_keys($options['columns']) as $c) {
            $tmp = explode(self::COLUMNALIAS, $c);
            $tmpalias = array_pop($tmp);
            $tmpcsv = count($tmp) ? implode(self::COLUMNALIAS, $tmp) : $tmpalias;
            $aliased[$tmpcsv] = [
                'name' => $tmpalias,
                'csv' => $tmpcsv,
                'full' => $c
            ];
        }
        return $aliased;
    }
}

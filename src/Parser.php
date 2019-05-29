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
    public function autoDetectLineEndings($value = true)
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
    public function fromFile($file, array $options = [])
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
    public function fromString($data, array $options = [])
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
    public function fromResource($data, array $options = [])
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
                    $rowData = $options['onRowParsed']($rowData, $index, $parsed, $options);
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
        if (!count($parsed)) {
            return $parsed;
        }
        return $this->postProcess($parsed, $columns, $options);
    }

    /**
     * Add post process behaviour for columns if needed
     *
     * @param array $data the processed data
     * @param array $columns columns metadata
     * @param array $options configuration options for parsing
     * @return array the processed data
     */
    private function postProcess(array $data, array $columns, array $options)
    {
        $keys = array_keys($data[0]);
        $result = [];
        foreach ($keys as $key) {
            $col = $columns['_' . array_search($key, array_column($columns, 'name', 'index'))];
            if ($options['columns'][$col['full']] instanceof OptimizerInterface) {
                $columnData = array_column($data, $key);
                $result[$key] = $options['columns'][$col['full']]->reduce($columnData, $col, $options);
            }
        }
        if (!count($result)) {
            return $data;
        }
        $resultKeys = array_keys($result);
        foreach ($data as $i => $row) {
            foreach ($resultKeys as $key) {
                $value = $data[$i][$key];
                $data[$i][$key] = isset($result[$key][$value]) ? $result[$key][$value] : $value;
            }
        }
        return $data;
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
    private function getRow(array $row, $index, array $columns, array $options)
    {
        $parsed = [];
        if ($options['strictHeaders'] && count($row) !== count($columns)) {
            throw new Exception("At line [$index], columns don't match headers", Exception::DIFFCOLUMNS);
        }
        foreach ($columns as $colinfo) {
            $colinfo['type'] = 'column';
            $i = $colinfo['index'];
            try {
                $col = isset($row[$i]) && !$colinfo['phantom'] ? $row[$i] : '';

                $col = $options['autotrim'] ? trim($col) : $col;
                if (is_callable($options['onBeforeColumnParse'])) {
                    $col = $options['onBeforeColumnParse']($col, $index, $colinfo, $options);
                }

                $method = isset($options['columns'][$colinfo['full']])
                    ? $options['columns'][$colinfo['full']]
                    : null;

                if ($method instanceof OptimizerInterface) {
                    $method = [$method, 'parse'];
                }
                $parsed[$colinfo['name']] = $method
                    ? $method($col, $index, $row, $parsed, $colinfo, $options)
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
    private function getColumns($data, array $options)
    {
        $csvHeaders = $this->getCsvHeaders($data, $options);
        $optionsHeaders = $this->getOptionsHeaders($options);

        $max = max($csvHeaders)['index'];
        $headers = [];
        foreach ($optionsHeaders as $key => $header) {
            if ($options['strictHeaders'] && !isset($csvHeaders[$key])) {
                throw new Exception("Header [$key] not found in CSV file", Exception::HEADERMISSING);
            }
            if (isset($csvHeaders[$key])) {
                $header['index'] = $csvHeaders[$key]['index'];
                $headers['_' . $header['index']] = $header;
            } else {
                // fake an index for columns defined in options configuration
                // that are not inside CSV file
                $max += 1;
                $header['index'] = $max;
                $header['phantom'] = true;
                $headers['_' . $max] = $header;
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
    private function getCsvHeaders($data, array $options)
    {
        $columns = fgetcsv($data, $options['length'], $options['delimiter'], $options['enclosure'], $options['escape']);
        if (!$options['headers']) {
            rewind($data);
        }
        if (!$columns || (count($columns) === 1 && $columns[0] === null)) {
            throw new Exception("CSV data is empty", Exception::EMPTYCONTENT);
        }
        $cols = array_map('trim', $options['headers'] ? $columns : array_keys($columns));
        $headers = [];
        foreach ($cols as $i => $h) {
            if (isset($headers[$h])) {
                throw new Exception("Header [$h] can't be the same for two columns", Exception::HEADEREXISTS);
            }
            $headers[$h] = [
                'csv' => $h,
                'index' => $i
            ];
        }
        return $headers;
    }

    /**
     * Get headers from columns configuration options
     *
     * @param array $options configuration options for parsing
     * @return array
     */
    private function getOptionsHeaders(array $options)
    {
        $aliased = [];
        foreach (array_keys($options['columns']) as $c) {
            $tmp = explode(self::COLUMNALIAS, $c);
            $alias = array_pop($tmp);
            $csv = count($tmp) ? implode(self::COLUMNALIAS, $tmp) : $alias;
            $aliased[$csv] = [
                'name' => $alias,
                'csv' => $csv,
                'full' => $c,
                'phantom' => false,
                'index' => null
            ];
        }
        return $aliased;
    }
}

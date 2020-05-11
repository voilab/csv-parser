<?php
namespace voilab\csv;

use Psr\Http\Message\StreamInterface;

class Parser
{
    /**
     * Column alias to be used in columns definitions
     * @var string
     */
    const COLUMNALIAS = ' as ';

    /**
     * Error texts translation
     * @var I18nInterface
     */
    private $i18n;

    /**
     * Last seek position in the resource
     * @var int
     */
    private $pointerPos;

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
        // PSR stream
        'lineEnding' => "\n",
        // headers
        'headers' => true,
        'strictHeaders' => true,
        'strictDefinedHeaders' => true,
        // big files
        'size' => 0,
        'start' => 0,
        'seek' => 0,
        // data pre-manipulation
        'autotrim' => true,
        'onBeforeColumnParse' => null,
        // data post-manipulation
        'onRowParsed' => null,
        'onError' => null,
        // column definition
        'columns' => [],
        'debug' => false
    ];

    /**
     * Get header name with alias. Produce "initialHeader as alias"
     *
     * @param string $csvHeader the csv header name
     * @param string $alias the alias of this header
     * @return string the column name
     */
    public static function alias($csvHeader, $alias)
    {
        return $csvHeader . static::COLUMNALIAS . $alias;
    }

    /**
     * Constructor of the CSV data parser.
     *
     * @param array $options default options for parsing
     * @param I18nInterface|null $i18n custom translations for errors
     */
    public function __construct(array $options = [], I18nInterface $i18n = null)
    {
        $this->options = array_merge($this->options, $options);
        $this->i18n = $i18n ?: new I18n();
    }

    /**
     * Set automatic detection for line endings, to deal with Mac line endings
     *
     * @param bool $value set or unset auto detect line endings
     * @return self
     */
    public function autoDetectLineEndings($value) : self
    {
        ini_set('auto_detect_line_endings', (bool) $value);
        return $this;
    }

    /**
     * Return last position parsed of the resource, if there's a [size] option.
     * This value can be passed to [seek] option to start exactely where it
     * ended.
     *
     * @return int
     */
    public function getPointerPosition() : int
    {
        return $this->pointerPos ?: 0;
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
            throw new Exception(sprintf($this->i18n->t('NOFILE'), $file), Exception::NOFILE);
        }
        $resource = fopen($file, 'r');
        $result = $this->parse(new CsvStream($resource), $options);
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
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $data);
        rewind($resource);
        $result = $this->parse(new CsvStream($resource), $options);
        fclose($resource);
        return $result;
    }

    /**
     * Parse a CSV data resource
     *
     * @param resource $data the CSV data resource
     * @param array $options configuration options for parsing
     * @return array the processed data
     */
    public function fromResource($data, array $options = []) : array
    {
        if (!is_resource($data)) {
            throw new Exception($this->i18n->t('NORESOURCE'), Exception::NORESOURCE);
        }
        return $this->parse(new CsvStream($data), $options);
    }

    /**
     * Parse a CSV stream
     *
     * @param StreamInterface $data the CSV stream
     * @param array $options configuration options for parsing
     * @return array the processed data
     */
    public function fromStream(StreamInterface $data, array $options = []) : array
    {
        return $this->parse(new Stream($data, $options), $options);
    }

    /**
     * Parse a stream that implements the StreamInterface
     *
     * @param CsvInterface $data the CSV data stream
     * @param array $options configuration options for parsing
     * @return array the processed data
     */
    public function parse(CsvInterface $data, array $options = []) : array
    {
        $options = array_merge($this->options, $options);
        if (!count($options['columns'])) {
            throw new Exception($this->i18n->t('NOCOLUMN'), Exception::NOCOLUMN);
        }
        // there're two ways to handle no-enclosure: same as separator or 0x00
        if (!$options['enclosure']) {
            $options['enclosure'] = 0x00;
        }

        $columns = $this->getColumns($data, $options);
        // seek directly at the right place
        if ($options['seek']) {
            $data->seek($options['seek']);
        }

        $parsed = [];
        $i = 0;
        if ($options['seek'] && $options['start']) {
            // if seek and start are defined, we can set the starting point
            // to what is defined
            $i = $options['start'];
        }
        while (
            (!$options['size'] || $i < $options['size'] + $options['start']) &&
            false !== ($row = $data->getCsv($options['length'], $options['delimiter'], $options['enclosure'], $options['escape']))
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
        $this->pointerPos = $data->tell();
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
    private function postProcess(array $data, array $columns, array $options) : array
    {
        $keys = array_keys($data[0]);
        $result = [];
        foreach ($keys as $key) {
            $found = array_search($key, array_column($columns, 'name', 'index'));
            if ($found === false) {
                continue;
            }
            $meta = $columns[$found];
            if (!$options['columns'][$meta['full']] instanceof OptimizerInterface) {
                continue;
            }
            $columnData = array_column($data, $key);
            try {
                $meta['type'] = 'reducer';
                $result[$key] = $options['columns'][$meta['full']]->reduce($columnData, $data, $result, $meta, $options);
                // set the reduce result in the main data array
                foreach ($data as $i => $row) {
                    $index = $i + ($options['headers'] ? 2 : 1);
                    $value = $data[$i][$key];
                    $meta['type'] = 'optimizer';
                    try {
                        $data[$i][$key] = isset($result[$key][$value])
                            ? $result[$key][$value]
                            : $options['columns'][$meta['full']]->absent($value, $index, $data[$i], $result, $meta, $options);

                    } catch (\Exception $e) {
                        if (is_callable($options['onError'])) {
                            $options['onError']($e, $index, $meta, $options);
                        } else {
                            throw $e;
                        }
                    }
                }
            } catch (\Exception $e) {
                if ($meta['type'] === 'optimizer') {
                    throw $e;
                }
                if (is_callable($options['onError'])) {
                    $options['onError']($e, null, $meta, $options);
                } else {
                    throw $e;
                }
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
    private function getRow(array $row, int $index, array $columns, array $options) : array
    {
        $parsed = [];
        if ($options['strictHeaders'] && count($row) !== count($columns)) {
            throw new Exception(sprintf($this->i18n->t('DIFFCOLUMNS'), $index), Exception::DIFFCOLUMNS);
        }
        foreach ($columns as $meta) {
            $meta['type'] = 'column';
            $i = $meta['index'];
            try {
                $col = isset($row[$i]) && !$meta['phantom'] ? $row[$i] : '';

                $col = $options['autotrim'] ? trim($col) : (string) $col;
                if (is_callable($options['onBeforeColumnParse'])) {
                    $col = $options['onBeforeColumnParse']($col, $index, $meta, $options);
                }

                $method = isset($options['columns'][$meta['full']])
                    ? $options['columns'][$meta['full']]
                    : null;

                if ($method instanceof OptimizerInterface) {
                    $method = [$method, 'parse'];
                }
                $parsed[$meta['name']] = $method
                    ? $method($col, $index, $row, $parsed, $meta, $options)
                    : $col;

            } catch (\Exception $e) {
                if (is_callable($options['onError'])) {
                    // user will decide what to do with the error
                    $options['onError']($e, $index, $meta, $options);
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
     * @param CsvInterface $data the CSV data resource
     * @param array $options configuration options for parsing
     * @return array the columns. If they are aliased, return the aliased ones
     */
    private function getColumns(CsvInterface $data, array $options) : array
    {
        $csvHeaders = $this->getCsvHeaders($data, $options);
        $optionsHeaders = $this->getOptionsHeaders($options);

        $max = count($csvHeaders);
        $headers = [];
        foreach ($optionsHeaders as $key => $header) {
            if (($options['strictHeaders'] || $options['strictDefinedHeaders']) && !isset($csvHeaders[$key])) {
                throw new Exception(sprintf($this->i18n->t('HEADERMISSING'), $key), Exception::HEADERMISSING);
            }
            if (isset($csvHeaders[$key])) {
                $header['index'] = $csvHeaders[$key]['index'];
                $headers[$header['index']] = $header;
            } else {
                // fake an index for columns defined in options configuration
                // that are not inside CSV resource
                $max += 1;
                $header['index'] = $max;
                $header['phantom'] = true;
                $headers[$max] = $header;
            }
        }
        return $headers;
    }

    /**
     * Get headers from CSV resource
     *
     * @param CsvInterface $data the CSV data resource
     * @param array $options configuration options for parsing
     * @return array
     */
    private function getCsvHeaders(CsvInterface $data, array $options) : array
    {
        $columns = $data->getCsv($options['length'], $options['delimiter'], $options['enclosure'], $options['escape']);
        if (!$options['headers']) {
            $data->rewind();
        }
        if (!$columns || (count($columns) === 1 && $columns[0] === null)) {
            throw new Exception($this->i18n->t('EMPTY'), Exception::EMPTY);
        }
        $cols = array_map('trim', $options['headers'] ? $columns : array_keys($columns));
        $headers = [];
        foreach ($cols as $i => $h) {
            // remove carriage returns and surnumeral spaces
            $h = preg_replace('/\s\s+/', ' ', str_replace(["\r\n", "\r", "\n"], ' ', $h));
            if (isset($headers[$h])) {
                throw new Exception(sprintf($this->i18n->t('HEADEREXISTS'), $h), Exception::HEADEREXISTS);
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
    private function getOptionsHeaders(array $options) : array
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

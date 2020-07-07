<?php
namespace voilab\csv;

use Psr\Http\Message\StreamInterface;

/**
 * This class is a simple wrapper for Psr StreamInterface, which add the other
 * required method: getCsv(), which is the one responsible for extracting one
 * line of the CSV and create an array out of it
 */
class CsvStream implements CsvInterface
{
    /**
     * Stream
     * @var StreamInterface
     */
    private $resource;

    /**
     * Options array
     * @var array
     */
    private $options = [
        'debug' => false,
        'lineEnding' => "\n"
    ];

    /**
     * Current buffer string
     * @var string
     */
    private $buffer = '';

    /**
     * Seek position which represents the beginning of a new line
     * @var int
     */
    private $index = 0;

    /**
     * Resource stream constructor
     *
     * @param StreamInterface $data
     * @param array $options
     */
    public function __construct(StreamInterface $data, array $options = [])
    {
        $this->resource = $data;
        $this->options = array_merge($this->options, $options);
    }

    public function __toString()
    {
        return $this->resource->__toString();
    }

    public function close()
    {
        $this->resource->close();
    }

    public function detach()
    {
        $this->buffer = '';
        $this->index = 0;
        $this->resource->detach();
    }

    public function getSize()
    {
        return $this->resource->getSize();
    }

    public function tell()
    {
        // returns the seek position, always at the beginning of a line
        return $this->index;
    }

    public function eof()
    {
        return $this->resource->eof();
    }

    public function isSeekable()
    {
        return $this->resource->isSeekable();
    }

    public function seek($offset, $whence = \SEEK_SET)
    {
        $this->buffer = '';
        $this->index = $offset;
        $this->resource->seek($offset, $whence);
    }

    public function rewind()
    {
        $this->buffer = '';
        $this->index = 0;
        $this->resource->rewind();
    }

    public function isWritable()
    {
        return $this->resource->isWritable();
    }

    public function write($string)
    {
        return $this->resource->write($string);
    }

    public function isReadable()
    {
        return $this->resource->isReadable();
    }

    public function read($length)
    {
        return $this->resource->read($length);
    }

    public function getContents()
    {
        return $this->resource->getContents();
    }

    public function getMetadata($key = null)
    {
        return $this->resource->getMetadata($key);
    }

    /**
     * @author Method largely inspired from https://github.com/offdev/csv
     * @inheritdoc
     */
    public function getCsv($length, $delimiter, $enclosure, $escape)
    {
        if (!$this->resource) {
            return null;
        }
        $remaining = $length - strlen($this->buffer);
        if ($remaining > 0 && !$this->resource->eof()) {
            $this->buffer .= $this->resource->read($remaining);
        }

        $pos = mb_strpos($this->buffer, $this->options['lineEnding']);
        $this->index = $this->index + $pos + mb_strlen($this->options['lineEnding']);
        if ($pos !== false || (!empty($this->buffer) && $this->resource->eof())) {
            $line = ($pos !== false)
                ? mb_substr($this->buffer, 0, $pos)
                : $this->buffer;

            $this->buffer = ($pos !== false)
                ? mb_substr($this->buffer, $pos + mb_strlen($this->options['lineEnding']))
                : '';

            return str_getcsv($line, $delimiter, $enclosure, $escape);
        }

        if (!$length || !empty($this->buffer)) {
            throw new \RuntimeException('Buffer length too small. No line ending found.');
        }
        return false;
    }
}

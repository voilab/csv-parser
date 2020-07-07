<?php
namespace voilab\csv;

/**
 * This class provides methods used to manipulate streams. It's exactely like
 * the Psr StreamInterface, but it has an other required method, getCsv(), which
 * is a wrapper for fgetcsv()
 */
class CsvSplFile implements CsvInterface
{
    /**
     * Stream metadata
     * @var array
     */
    private $meta = [];

    /**
     * File statistics
     * @var array
     */
    private $stat;

    /**
     * Stream resource
     * @var \SplFileObject
     */
    private $resource;

    /**
     * Options array
     * @var array
     */
    private $options = [
        'debug' => false,
        'metadata' => []
    ];

    /**
     * Resource stream constructor
     *
     * @param \SplFileObject $data
     * @param array $options the options array
     */
    public function __construct(\SplFileObject $data, array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->resource = $data;
        $this->meta = $this->options['metadata'];
        $this->stat = $data->fstat();
    }

    /**
     * Returns underlying resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function __toString()
    {
        try {
            $this->rewind();
            return $this->getContents();
        } catch (\Exception $e) {
            return '[toString error : ' . $e->getMessage() . ']';
        }
    }

    public function close()
    {
        $this->detach();
    }

    public function detach()
    {
        $this->meta = [];
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function getSize()
    {
        return $this->resource->getSize();
    }

    public function tell()
    {
        if (!$this->resource) {
            return;
        }
        return $this->resource->key();
    }

    public function eof()
    {
        return !$this->resource || $this->resource->eof();
    }

    public function isSeekable()
    {
        // SplFileObject implements SeekableIterator
        return true;
    }

    public function seek($offset, $whence = \SEEK_SET)
    {
        if (!$this->isSeekable()) {
            return;
        }
        $this->resource->seek($offset);
    }

    public function rewind()
    {
        if (!$this->resource) {
            return;
        }
        $this->resource->rewind();
    }

    public function isWritable()
    {
        return $this->resource->isWritable();
    }

    public function write($string)
    {
        if (!$this->isWritable()) {
            return 0;
        }
        $result = $this->resource->fwrite($string);
        if ($result === false) {
            throw new \RuntimeException('Stream is not writable');
        }
        return $result;
    }

    public function isReadable()
    {
        return $this->resource->isReadable();
    }

    public function read($length) : string
    {
        if (!$this->isReadable()) {
            return '';
        }
        $result = $this->resource->fread($length);
        if ($result === false) {
            throw new \RuntimeException('Stream not readable');
        }
        return $result;
    }

    public function getContents()
    {
        if (!$this->resource) {
            return '';
        }
        $result = $this->resource->fread($this->resource->getSize());
        if ($result === false) {
            throw new \RuntimeException('Unable to get stream content');
        }
        return $result;
    }

    public function getMetadata($key = null)
    {
        return $key !== null
            ? (isset($this->meta[$key]) ? $this->meta[$key] : null)
            : $this->meta;
    }

    public function getCsv($length, $delimiter, $enclosure, $escape)
    {
        if (!$this->resource) {
            return null;
        }
        $data = $this->resource->fgetcsv($delimiter, $enclosure, $escape);
        // empty line
        if ($data && count($data) === 1 && !$data[0]) {
            return false;
        }
        // pointer overflow
        if ($data === null) {
            return false;
        }
        return $data;
    }
}

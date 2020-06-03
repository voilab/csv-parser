<?php
namespace voilab\csv;

/**
 * This class provides methods used to manipulate streams. It's exactely like
 * the Psr StreamInterface, but it has an other required method, getCsv(), which
 * is a wrapper for fgetcsv()
 */
class CsvResource implements CsvInterface
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
     * @var resource
     */
    private $resource;

    /**
     * File mode hash
     * @var array
     */
    private $readWriteHash = [
        'r' => [
            'r', 'r+', 'w+', 'a+', 'x+', 'c+',
            'rb', 'r+b', 'w+b', 'a+b', 'x+b', 'c+b',
            'rt', 'r+t', 'w+t', 'a+t', 'x+t', 'c+t'
        ],
        'w' => [
            'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+',
            'r+b', 'wb', 'w+b', 'ab', 'a+b', 'xb', 'x+b', 'cb', 'c+b',
            'r+t', 'wt', 'w+t', 'at', 'a+t', 'xt', 'x+t', 'ct', 'c+t'
        ]
    ];

    /**
     * Resource stream constructor
     *
     * @param resource $data
     */
    public function __construct($data)
    {
        if (!is_resource($data)) {
            throw new \RuntimeException('Data is not a resource');
        }
        $this->resource = $data;
        $this->meta = stream_get_meta_data($data);
        $this->stat = fstat($data);
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

    public function __toString() : string
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
        if ($this->resource && is_resource($this->resource)) {
            fclose($this->resource);
        }
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
        return isset($this->stat['size']) ? $this->stat['size'] : null;
    }

    public function tell()
    {
        if (!$this->resource) {
            return;
        }
        $result = ftell($this->resource);
        if ($result === false) {
            throw new \RuntimeException('Unable to get current stream position');
        }
        return $result;
    }

    public function eof()
    {
        return !$this->resource || feof($this->resource);
    }

    public function isSeekable() : bool
    {
        return isset($this->meta['seekable']) ? $this->meta['seekable'] : false;
    }

    public function seek($offset, $whence = \SEEK_SET)
    {
        if (!$this->isSeekable()) {
            return;
        }
        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new \RuntimeException('Stream is not seekable');
        }
    }

    public function rewind()
    {
        if (!$this->resource) {
            return;
        }
        if (rewind($this->resource) === false) {
            throw new \RuntimeException('Unable to rewind stream');
        }
    }

    public function isWritable() : bool
    {
        $m = $this->meta;
        return isset($m['mode']) && in_array($m['mode'], $this->readWriteHash['w']);
    }

    public function write($string) : int
    {
        if (!$this->isWritable()) {
            return 0;
        }
        $result = fwrite($this->resource, $string);
        if ($result === false) {
            throw new \RuntimeException('Stream is not writable');
        }
        return $result;
    }

    public function isReadable() : bool
    {
        $m = $this->meta;
        return isset($m['mode']) && in_array($m['mode'], $this->readWriteHash['r']);
    }

    public function read($length) : string
    {
        if (!$this->isReadable()) {
            return '';
        }
        $result = fread($this->resource, $length);
        if ($result === false) {
            throw new \RuntimeException('Stream not readable');
        }
        return $result;
    }

    public function getContents() : string
    {
        if (!$this->resource) {
            return '';
        }
        $result = stream_get_contents($this->resource);
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
        return fgetcsv($this->resource, $length, $delimiter, $enclosure, $escape);
    }
}

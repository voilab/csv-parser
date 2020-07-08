<?php
namespace voilab\csv;

/**
 * This class provides methods used to manipulate arrays as streams.
 */
class CsvIterable implements CsvInterface
{
    /**
     * Options array
     * @var array
     */
    private $options = [
        'metadata' => []
    ];

    /**
     * Array iterable resource
     * @var iterable
     */
    private $resource;

    /**
     * Resource iterable constructor
     *
     * @param iterable $data
     * @param array $options the options array
     */
    public function __construct(iterable $data, array $options = [])
    {
        $this->resource = $data;
        $this->options = array_merge($this->options, $options);
    }

    public function setMetadata(string $key, $value) 
    {
        $this->options[$key] = $value;
    }

    /**
     * Returns underlying resource
     *
     * @return iterable
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
        $this->detach();
    }

    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    public function getSize()
    {
        if (!$this->resource) {
            return;
        }
        $current = key($this->resource);
        end($this->resource);
        $last = key($this->resource);
        reset($this->resource);
        while (!$this->eof() && key($this->resource) !== $current) {
            next($this->resource);
        }
        return $last + 1;
    }

    public function tell()
    {
        if (!$this->resource) {
            return;
        }
        return key($this->resource);
    }

    public function eof()
    {
        return !$this->resource || current($this->resource) === false;
    }

    public function isSeekable() : bool
    {
        return !!$this->resource;
    }

    public function seek($offset, $whence = \SEEK_SET)
    {
        if (!$this->isSeekable()) {
            return;
        }
        if ($whence === \SEEK_END) {
            end($this->resource);
            $offset = key($this->resource) + $offset;
            while (!$this->eof() && key($this->resource) !== $offset && $offset !== 0) {
                prev($this->resource);
            }
            return;
        }
        if ($whence === \SEEK_SET) {
            $this->rewind();
        } elseif ($whence === \SEEK_CUR) {
            $offset += key($this->resource);
        }
        while (!$this->eof() && key($this->resource) !== $offset) {
            next($this->resource);
        }
    }

    public function rewind()
    {
        if (!$this->resource) {
            return;
        }
        reset($this->resource);
    }

    public function isWritable() : bool
    {
        return false;
    }

    public function write($string) : int
    {
        throw new \RuntimeException('Iterable is not writable. Function not implemented');
    }

    public function isReadable() : bool
    {
        return !!$this->resource;
    }

    public function read($length) : string
    {
        if (!$this->isReadable()) {
            return '';
        }
        return current($this->resource);
    }

    public function getContents() : string
    {
        if (!$this->resource) {
            return '';
        }
        return 'Iterable()';
    }

    public function getMetadata($key = null)
    {
        $meta = $this->options['metadata'];
        return $key !== null
            ? (isset($meta[$key]) ? $meta[$key] : null)
            : $meta;
    }

    public function getCsv($length, $delimiter, $enclosure, $escape)
    {
        if (!$this->resource) {
            return null;
        }
        $result = current($this->resource);
        next($this->resource);
        return $result;
    }
}

<?php

use PHPUnit\Framework\TestCase;

final class SeekTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                },
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $this->file = __DIR__ . '/fixtures/csv-seek.csv';
    }

    public function testSeek() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'start' => 0,
            'size' => 3
        ]);

        $result = $this->parser->fromFile($this->file, [
            'size' => 3,
            'seek' => $this->parser->getPointerPosition()
        ]);

        $expect = [
            [ 'A' => 4, 'B' => 'test 4' ],
            [ 'A' => 5, 'B' => 'test 5' ],
            [ 'A' => 6, 'B' => 'test 6' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testSeekWithStartUnsync() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'start' => 0,
            'size' => 3
        ]);

        $result = $this->parser->fromFile($this->file, [
            'size' => 1,
            'seek' => $this->parser->getPointerPosition(),
            'onRowParsed' => function ($data, $index) {
                $this->assertEquals($index, 2);
                return $data;
            }
        ]);

        $expect = [
            [ 'A' => 4, 'B' => 'test 4' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testSeekWithStartSync() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'start' => 0,
            'size' => 3
        ]);
        $expect = [
            [ 'A' => 1, 'B' => 'test 1' ],
            [ 'A' => 2, 'B' => 'test 2' ],
            [ 'A' => 3, 'B' => 'test 3' ]
        ];
        $this->assertEquals($result, $expect);

        $result = $this->parser->fromFile($this->file, [
            'size' => 1,
            'start' => 3,
            'seek' => $this->parser->getPointerPosition(),
            'onError' => function ($e, $index, $meta) {
                $this->assertEquals($index, 5);
            },
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                },
                'B' => function (string $data) {
                    throw new \Exception('stop');
                }
            ]
        ]);
    }

    public function testSeekPointerOverflow() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'start' => 0,
            'size' => 3
        ]);

        $result = $this->parser->fromFile($this->file, [
            'size' => 3,
            'seek' => 500000
        ]);

        $expect = [];
        $this->assertEquals($result, $expect);
    }
}

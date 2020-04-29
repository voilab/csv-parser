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
            'seek' => $this->parser->getLastSeek()
        ]);

        $expect = [
            [ 'A' => 4, 'B' => 'test 4' ],
            [ 'A' => 5, 'B' => 'test 5' ],
            [ 'A' => 6, 'B' => 'test 6' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testSeekWithStart() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'start' => 0,
            'size' => 3
        ]);

        $result = $this->parser->fromFile($this->file, [
            'size' => 3,
            'start' => 6,
            'seek' => $this->parser->getLastSeek()
        ]);

        $expect = [
            [ 'A' => 4, 'B' => 'test 4' ],
            [ 'A' => 5, 'B' => 'test 5' ],
            [ 'A' => 6, 'B' => 'test 6' ]
        ];
        $this->assertEquals($result, $expect);
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

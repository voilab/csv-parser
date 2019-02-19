<?php

use PHPUnit\Framework\TestCase;

final class StartSizeTest extends TestCase
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
        $this->file = __DIR__ . '/fixtures/csv-start-size.csv';
    }

    public function testStartBegining() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'start' => 0,
            'size' => 2
        ]);
        $expect = [
            [ 'A' => 1, 'B' => 'test 1' ],
            [ 'A' => 2, 'B' => 'test 2' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testStartMiddle() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'start' => 5,
            'size' => 3
        ]);
        $expect = [
            [ 'A' => 6, 'B' => 'test 6' ],
            [ 'A' => 7, 'B' => 'test 7' ],
            [ 'A' => 8, 'B' => 'test 8' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testStartEnding() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'start' => 8,
            'size' => 3
        ]);
        $expect = [
            [ 'A' => 9, 'B' => 'test 9' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

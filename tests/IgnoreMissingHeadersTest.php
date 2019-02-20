<?php

use PHPUnit\Framework\TestCase;

final class IgnoreMissingHeadersTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'ignoreMissingHeaders' => true
        ]);
        $this->file = __DIR__ . '/fixtures/csv-ignore-missing-headers.csv';
    }

    public function testMissingHeader() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'strictHeaders' => false,
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                }
            ]
        ]);
        $expect = [
            [ 'A' => 4 ],
            [ 'A' => 9 ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testMissingHeaderWithStrictOption() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'strictHeaders' => true,
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                }
            ]
        ]);
        $expect = [
            [ 'A' => 4 ],
            [ 'A' => 9 ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testMissingHeaderWithNoHeaderLine() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'headers' => false,
            'columns' => [
                0 => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 'A' ],
            [ '4' ],
            [ '9' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

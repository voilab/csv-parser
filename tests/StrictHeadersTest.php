<?php

use PHPUnit\Framework\TestCase;
use voilab\csv\Exception;

final class StrictHeadersTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strictHeaders' => true,
            'strictDefinedHeaders' => false
        ]);
        $this->file = __DIR__ . '/fixtures/csv-strict-headers.csv';
    }

    public function testMissingHeader() : void
    {
        $this->expectExceptionCode(Exception::DIFFCOLUMNS);
        $result = $this->parser->fromFile($this->file, [
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                }
            ]
        ]);
    }

    public function testWrongHeader() : void
    {
        $this->expectExceptionCode(Exception::HEADERMISSING);
        $result = $this->parser->fromFile($this->file, [
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                },
                'C' => function (string $data) {
                    return (int) $data;
                }
            ]
        ]);
    }

    public function testMissingHeaderIgnore() : void
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

    public function testWrongHeaderIgnore() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'strictHeaders' => false,
            'columns' => [
                'C' => function (string $data) {
                    return $data;
                },
                'A' => function (string $data) {
                    return (int) $data;
                }
            ]
        ]);
        $expect = [
            [ 'C' => '', 'A' => 4 ],
            [ 'C' => '', 'A' => 9 ]
        ];
        $this->assertEquals($result, $expect);
    }
}

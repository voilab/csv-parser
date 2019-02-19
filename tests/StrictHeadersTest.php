<?php

use PHPUnit\Framework\TestCase;
use voilab\csv\Exception;

final class StrictHeadersTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strictHeaders' => true
        ]);
        $this->file = __DIR__ . '/fixtures/csv-strict-headers.csv';
    }

    public function testMissingHeader() : void
    {
        $this->expectExceptionCode(Exception::HEADERMISSING);
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

    public function testMissingHeaderAdded() : void
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
            [ 'A' => 4, 'B' => 'hello' ],
            [ 'A' => 9, 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

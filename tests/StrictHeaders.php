<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;
use voilab\csv\Exception;

class StrictHeaders extends TestCase
{
    protected function setUp() : void
    {
        $this->dir = __DIR__ . '/fixtures';
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strict' => true
        ]);
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testMissingHeader() : void
    {
        $this->expectExceptionCode(Exception::DIFFCOLUMNS);
        $result = $this->parser->parse($this->resource, [
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                }
            ]
        ]);
    }

    public function testMissingHeaderIgnore() : void
    {
        $result = $this->parser->parse($this->resource, [
            'strict' => false,
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
        $result = $this->parser->parse($this->resource, [
            'strict' => false,
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

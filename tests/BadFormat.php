<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;
use voilab\csv\Exception;

class BadFormat extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'columns' => [
                'A' => function (string $data) {
                    return $data;
                },
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $this->dir = __DIR__ . '/fixtures';
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testDoubleHeaders() : void
    {
        $this->expectExceptionCode(Exception::HEADEREXISTS);
        $result = $this->parser->parse($this->resource);
    }

    public function testEmpty() : void
    {
        $this->expectExceptionCode(Exception::EMPTY);
        $result = $this->parser->parse($this->resource);
    }

    public function testBuggyStrict() : void
    {
        $this->expectExceptionCode(Exception::DIFFCOLUMNS);
        $result = $this->parser->parse($this->resource, [
            'strict' => true
        ]);
    }

    public function testBuggyLoose() : void
    {
        $result = $this->parser->parse($this->resource, [
            'strict' => false,
            'columns' => [
                'A' => function (string $data) {
                    return $data;
                },
                'B' => function (string $data) {
                    return $data;
                },
                'D' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 'A' => 4, 'B' => 'hello', 'D' => 'x' ],
            [ 'A' => 9, 'B' => 'world', 'D' => '' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

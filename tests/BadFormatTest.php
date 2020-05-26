<?php

use PHPUnit\Framework\TestCase;
use voilab\csv\Exception;

final class BadFormatTest extends TestCase
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
        $this->dir = __DIR__ . '/fixtures/';
    }

    public function testDoubleHeaders() : void
    {
        $this->expectExceptionCode(Exception::HEADEREXISTS);
        $result = $this->parser->fromFile($this->dir . 'csv-badformat-double-header.csv');
    }

    public function testEmpty() : void
    {
        $this->expectExceptionCode(Exception::EMPTY);
        $result = $this->parser->fromFile($this->dir . 'csv-badformat-empty.csv');
    }

    public function testNoFile() : void
    {
        $this->expectExceptionCode(Exception::NOFILE);
        $result = $this->parser->fromFile($this->dir . 'csv-badformat-nofile.csv');
    }

    public function testNoResource() : void
    {
        $this->expectExceptionCode(Exception::NORESOURCE);
        $result = $this->parser->fromResource('test');
    }

    public function testBuggyStrict() : void
    {
        $this->expectExceptionCode(Exception::DIFFCOLUMNS);
        $result = $this->parser->fromFile($this->dir . 'csv-badformat-buggy.csv', [
            'strict' => true
        ]);
    }

    public function testBuggyLoose() : void
    {
        $result = $this->parser->fromFile($this->dir . 'csv-badformat-buggy.csv', [
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

<?php

use PHPUnit\Framework\TestCase;
use voilab\csv\Exception;

final class StrictDefinedHeadersTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strictHeaders' => false
        ]);
        $this->file = __DIR__ . '/fixtures/csv-strict-defined-headers.csv';
    }

    public function testDefault() : void
    {
        $this->expectExceptionCode(Exception::HEADERMISSING);
        $result = $this->parser->fromFile($this->file, [
            'columns' => [
                'A' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testStrict() : void
    {
        $this->expectExceptionCode(Exception::HEADERMISSING);
        $result = $this->parser->fromFile($this->file, [
            'strictDefinedHeaders' => true,
            'columns' => [
                'A' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testNotStrict() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'strictDefinedHeaders' => false,
            'columns' => [
                'A' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; }
            ]
        ]);
        $expect = [
            [ 'A' => 4, 'C' => '' ],
            [ 'A' => 9, 'C' => '' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

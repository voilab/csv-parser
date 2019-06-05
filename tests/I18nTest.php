<?php

use PHPUnit\Framework\TestCase;

final class I18nTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';'
        ]);
        $this->dir =  __DIR__ . '/fixtures/';
    }

    public function testNoFile() : void
    {
        $this->expectExceptionMessage("File [berthe.csv] doesn't exist");
        $result = $this->parser->fromFile('berthe.csv', [
            'columns' => [
                'A' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testNoResource() : void
    {
        $this->expectExceptionMessage("CSV data must be a resource");
        $result = $this->parser->fromResource('error', [
            'columns' => [
                'A' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testNoColumn() : void
    {
        $this->expectExceptionMessage("No column configured in options");
        $result = $this->parser->fromFile($this->dir . 'csv-parser.csv');
    }

    public function testHeaderMissing() : void
    {
        $this->expectExceptionMessage("Header [Z] not found in CSV resource");
        $result = $this->parser->fromFile($this->dir . 'csv-parser.csv', [
            'columns' => [
                'Z' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testEmpty() : void
    {
        $this->expectExceptionMessage("CSV data is empty");
        $result = $this->parser->fromFile($this->dir . 'csv-badformat-empty.csv', [
            'columns' => [
                'Z' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testHeaderExists() : void
    {
        $this->expectExceptionMessage("Header [A] can't be the same for two columns");
        $result = $this->parser->fromFile($this->dir . 'csv-badformat-double-header.csv', [
            'columns' => [
                'Z' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testDiffColumns() : void
    {
        $this->expectExceptionMessage("At line [2], columns don't match headers");
        $result = $this->parser->fromFile($this->dir . 'csv-badformat-buggy.csv', [
            'columns' => [
                'A' => function (string $data) { return $data; }
            ]
        ]);
    }
}

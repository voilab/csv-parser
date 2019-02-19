<?php

use PHPUnit\Framework\TestCase;

final class OnBeforeColumnParse extends TestCase
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
        $this->file = __DIR__ . '/fixtures/csv-on-before-column-parse.csv';
    }

    public function testAddPrefix() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'onBeforeColumnParse' => function (string $data) {
                return 'prefix ' . $data;
            }
        ]);
        $expect = [
            [ 'A' => 'prefix 4', 'B' => 'prefix hello' ],
            [ 'A' => 'prefix 9', 'B' => 'prefix world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testAddPrefixToFirstColumn() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'onBeforeColumnParse' => function (string $data, $index, $col) {
                if ($col['name'] === 'A') {
                    return 'prefix ' . $data;
                }
                return $data;
            }
        ]);
        $expect = [
            [ 'A' => 'prefix 4', 'B' => 'hello' ],
            [ 'A' => 'prefix 9', 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testAddPrefixIndex() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'onBeforeColumnParse' => function (string $data, $index) {
                return $index . ' ' . $data;
            }
        ]);
        $expect = [
            [ 'A' => '2 4', 'B' => '2 hello' ],
            [ 'A' => '3 9', 'B' => '3 world' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

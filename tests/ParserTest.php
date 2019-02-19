<?php

use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';'
        ]);
        $this->file = __DIR__ . '/fixtures/csv-parser.csv';
    }

    public function testParseFromString() : void
    {
        $result = $this->parser->fromString(file_get_contents($this->file), [
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                },
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 'A' => 4, 'B' => 'hello' ],
            [ 'A' => 9, 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testAliasedColumns() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'columns' => [
                'A as id' => function (string $data) {
                    return (int) $data;
                },
                'B as content' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 'id' => 4, 'content' => 'hello' ],
            [ 'id' => 9, 'content' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testAliasedSpecialColumns() : void
    {
        $csv = "A as A1;B as B1\n4;hello\n9;world";
        $result = $this->parser->fromString($csv, [
            'columns' => [
                'A as A1 as id' => function (string $data) {
                    return (int) $data;
                },
                'B as B1 as content' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 'id' => 4, 'content' => 'hello' ],
            [ 'id' => 9, 'content' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testShuffleColumns() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'columns' => [
                'B as content' => function (string $data) {
                    return $data;
                },
                'A as id' => function (string $data) {
                    return (int) $data;
                }
            ]
        ]);
        $expect = [
            [ 'id' => 4, 'content' => 'hello' ],
            [ 'id' => 9, 'content' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

<?php

use PHPUnit\Framework\TestCase;

final class NoHeaderTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';'
        ]);
        $this->file = __DIR__ . '/fixtures/csv-no-header.csv';
    }

    public function testNoHeader() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'headers' => false,
            'columns' => [
                0 => function (string $data) {
                    return (int) $data;
                },
                1 => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 4, 'hello' ],
            [ 9, 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testNoHeaderWithAlias() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'headers' => false,
            'columns' => [
                '0 as id' => function (string $data) {
                    return (int) $data;
                },
                '1 as content' => function (string $data) {
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
}

<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class Headers extends TestCase
{
    protected function setUp() : void
    {
        $this->dir = __DIR__ . '/fixtures';
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';'
        ]);
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testNoHeader() : void
    {
        $result = $this->parser->parse($this->resource, [
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
        $result = $this->parser->parse($this->resource, [
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

    public function testCarriageReturnHeaders() : void
    {
        $result = $this->parser->parse($this->resource, [
            'delimiter' => ',',
            'enclosure' => '"',
            'columns' => [
                'header with carriage return as id' => function (string $data) {
                    return (int) $data;
                },
                'other test as content' => function (string $data) {
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

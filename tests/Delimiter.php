<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class Delimiter extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                },
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $this->dir = __DIR__ . '/fixtures';
        $this->expect = [
            [ 'A' => 4, 'B' => 'hello' ],
            [ 'A' => 9, 'B' => 'world' ]
        ];
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testSemiColon() : void
    {
        $result = $this->parser->parse($this->resource, [
            'delimiter' => ';'
        ]);
        $this->assertEquals($result, $this->expect);
    }

    public function testComma() : void
    {
        $result = $this->parser->parse($this->resource, [
            'delimiter' => ','
        ]);
        $this->assertEquals($result, $this->expect);
    }

    public function testTab() : void
    {
        $result = $this->parser->parse($this->resource, [
            'delimiter' => "\t"
        ]);
        $this->assertEquals($result, $this->expect);
    }
}

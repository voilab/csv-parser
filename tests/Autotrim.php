<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class Autotrim extends TestCase
{
    protected function setUp() : void
    {
        $this->dir = __DIR__ . '/fixtures';
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
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testTrimDefault() : void
    {
        $result = $this->parser->parse($this->resource);
        $expect = [
            [ 'A' => '4', 'B' => 'hello' ],
            [ 'A' => '9', 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testTrimActive() : void
    {
        $result = $this->parser->parse($this->resource, [
            'autotrim' => true
        ]);
        $expect = [
            [ 'A' => '4', 'B' => 'hello' ],
            [ 'A' => '9', 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testTrimInactive() : void
    {
        $result = $this->parser->parse($this->resource, [
            'autotrim' => false
        ]);
        $expect = [
            [ 'A' => ' 4 ', 'B' => ' hello' ],
            [ 'A' => '9 ', 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

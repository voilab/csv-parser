<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class Enclosure extends TestCase
{
    protected function setUp() : void
    {
        $this->dir = __DIR__ . '/fixtures';
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ','
        ]);
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testEnclosure() : void
    {
        $result = $this->parser->parse($this->resource, [
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

    public function testEnclosureDisabled() : void
    {
        $result = $this->parser->parse($this->resource, [
            'enclosure' => '',
            'columns' => [
                '"A" as A' => function (string $data) {
                    return $data;
                },
                '"B" as B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 'A' => '"4"', 'B' => '"hello"' ],
            [ 'A' => '"9"', 'B' => '"world"' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

<?php

use PHPUnit\Framework\TestCase;

final class EnclosureTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ','
        ]);
        $this->file = __DIR__ . '/fixtures/csv-enclosure.csv';
    }

    public function testEnclosure() : void
    {
        $result = $this->parser->fromFile($this->file, [
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
        $result = $this->parser->fromFile($this->file, [
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

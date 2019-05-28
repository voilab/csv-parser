<?php

use PHPUnit\Framework\TestCase;

final class AutotrimTest extends TestCase
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
        $this->file = __DIR__ . '/fixtures/csv-autotrim.csv';
    }

    public function testTrimDefault() : void
    {
        $result = $this->parser->fromFile($this->file);
        $expect = [
            [ 'A' => '4', 'B' => 'hello' ],
            [ 'A' => '9', 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testTrimActive() : void
    {
        $result = $this->parser->fromFile($this->file, [
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
        $result = $this->parser->fromFile($this->file, [
            'autotrim' => false
        ]);
        $expect = [
            [ 'A' => ' 4 ', 'B' => ' hello' ],
            [ 'A' => '9 ', 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

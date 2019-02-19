<?php

use PHPUnit\Framework\TestCase;

final class DelimiterTest extends TestCase
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
        $this->csv = file_get_contents(__DIR__ . '/fixtures/csv-delimiter.csv');
        $this->expect = [
            [ 'A' => 4, 'B' => 'hello' ],
            [ 'A' => 9, 'B' => 'world' ]
        ];
    }

    public function testSemiColon() : void
    {
        $result = $this->parser->fromString($this->csv, [
            'delimiter' => ';'
        ]);
        $this->assertEquals($result, $this->expect);
    }

    public function testComma() : void
    {
        $csv = str_replace(';' , ',', $this->csv);
        $result = $this->parser->fromString($csv, [
            'delimiter' => ','
        ]);
        $this->assertEquals($result, $this->expect);
    }

    public function testTab() : void
    {
        $csv = str_replace(';' , "\t", $this->csv);
        $result = $this->parser->fromString($csv, [
            'delimiter' => "\t"
        ]);
        $this->assertEquals($result, $this->expect);
    }
}

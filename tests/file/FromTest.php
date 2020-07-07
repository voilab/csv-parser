<?php
namespace voilab\csv\test\file;

use PHPUnit\Framework\TestCase;

final class FromTest extends TestCase
{
    protected function setUp() : void
    {
        $this->file = __DIR__ . '/../fixtures/csv-parser.csv';
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

    public function testFromFileMethod() : void
    {
        $result = $this->parser->fromFile($this->file);
        $expect = [
            [ 'A' => '4', 'B' => 'hello' ],
            [ 'A' => '9', 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testFromStringMethod() : void
    {
        $result = $this->parser->fromString(file_get_contents($this->file));
        $expect = [
            [ 'A' => '4', 'B' => 'hello' ],
            [ 'A' => '9', 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

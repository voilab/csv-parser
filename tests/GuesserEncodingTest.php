<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class GuesserEncodingTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strict' => false
        ]);
        $this->dir = __DIR__ . '/fixtures';
    }

    public function testEncodingIso88591ToUtf8() : void
    {
        $result = $this->parser->fromFile($this->dir .'/csv-guesser-encoding-iso8859-1.csv', [
            'guessEncoding' => new \voilab\csv\GuesserEncoding([
                'to' => 'utf-8',
                'encodings' => [
                    'iso-8859-1',
                    'utf-8'
                ]
            ]),
            'columns' => [
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $this->assertEquals($result[0]['B'], 'éolienne');
        $this->assertEquals($result[1]['B'], 'Maçon');
    }
}

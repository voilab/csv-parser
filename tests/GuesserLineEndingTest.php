<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class GuesserLineEndingTest extends TestCase
{
    protected function setUp() : void
    {
        $this->guesser = new \voilab\csv\GuesserLineEnding();
        $this->options = [
            'enclosure' => '"'
        ];
        $this->dir = __DIR__ . '/fixtures';
    }

    public function testLineEndingN() : void
    {
        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-n.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, "\n");
    }

    public function testLineEndingRN() : void
    {
        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-rn.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, "\r\n");
    }
}

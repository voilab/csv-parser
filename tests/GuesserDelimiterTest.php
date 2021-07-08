<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class GuesserDelimiterTest extends TestCase
{
    protected function setUp() : void
    {
        $this->guesser = new \voilab\csv\GuesserDelimiter();
        $this->options = [
            'length' => 1024,
            'enclosure' => '"',
            'escape' => '\\',
            'columns' => [
                'A' => function ($data) {
                    return $data;
                },
                'B' => function ($data) {
                    return $data;
                }
            ]
        ];
        $this->dir = __DIR__ . '/fixtures';
    }

    public function testDelimiterColon() : void
    {
        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-delim-colon.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, ':');
    }

    public function testDelimiterSemiColon() : void
    {
        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-delim-semicolon.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, ';');
    }

    public function testDelimiterComa() : void
    {
        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-delim-coma.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, ',');
    }

    public function testDelimiterSpace() : void
    {
        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-delim-space.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, ' ');
    }

    public function testDelimiterTab() : void
    {
        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-delim-tab.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, "\t");
    }

    public function testDelimiterPipe() : void
    {
        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-delim-pipe.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, '|');
    }

    public function testDelimiterAmbiguous() : void
    {
        $this->expectExceptionMessage('Ambiguous delimiters: found [,, ;] eligible!');

        $resource = new \voilab\csv\CsvFile($this->dir .'/csv-guesser-delim-ambiguous.csv');
        $result = $this->guesser->guess($resource, $this->options);
        $this->assertEquals($result, '|');
    }
}

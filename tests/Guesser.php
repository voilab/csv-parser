<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class Guesser extends TestCase
{
    protected function setUp() : void
    {
        $this->guesser = new \voilab\csv\Guesser();
        $this->dir = __DIR__ . '/fixtures';
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testLineEndingN() : void
    {
        $result = $this->guesser->guessLineEnding($this->resource);
        $this->assertEquals($result, "\n");
    }

    public function testLineEndingRN() : void
    {
        $result = $this->guesser->guessLineEnding($this->resource);
        $this->assertEquals($result, "\r\n");
    }
}

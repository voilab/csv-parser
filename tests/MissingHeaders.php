<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class MissingHeaders extends TestCase
{
    protected function setUp() : void
    {
        $this->dir = __DIR__ . '/fixtures';
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strict' => false
        ]);
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testShuffle() : void
    {
        $result = $this->parser->parse($this->resource, [
            'columns' => [
                'D' => function (string $data) { return $data; },
                'A' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; },
                'B' => function (string $data) { return $data; }
            ]
        ]);
        $expect = [[ 'D' => 'v', 'A' => '4', 'C' => 'x', 'B' => 'hello' ]];
        $this->assertEquals($result, $expect);
    }

    public function testShuffleWithIgnore() : void
    {
        $result = $this->parser->parse($this->resource, [
            'columns' => [
                'D' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; },
                'B' => function (string $data) { return $data; }
            ]
        ]);
        $expect = [[ 'D' => 'v', 'C' => 'x', 'B' => 'hello' ]];
        $this->assertEquals($result, $expect);
    }

    public function testShuffleWithMissing() : void
    {
        $result = $this->parser->parse($this->resource, [
            'columns' => [
                'D' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; },
                'Z' => function (string $data, $i, $row, $p, $meta) {
                    if ($meta['phantom']) {
                        return 'phantom';
                    }
                    return $data;
                }
            ]
        ]);
        $expect = [[ 'D' => 'v', 'C' => 'x', 'Z' => 'phantom' ]];
        $this->assertEquals($result, $expect);
    }
}

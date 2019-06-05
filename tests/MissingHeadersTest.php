<?php

use PHPUnit\Framework\TestCase;

final class MissingHeadersTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strictHeaders' => false,
            'strictDefinedHeaders' => false
        ]);
        $this->file = __DIR__ . '/fixtures/csv-missing-headers.csv';
    }

    public function testShuffle() : void
    {
        $result = $this->parser->fromFile($this->file, [
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
        $result = $this->parser->fromFile($this->file, [
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
        $result = $this->parser->fromFile($this->file, [
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

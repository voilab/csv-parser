<?php

use PHPUnit\Framework\TestCase;

final class ChunkParsedTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strict' => false
        ]);
        $this->file = __DIR__ . '/fixtures/csv-chunk-parsed.csv';
    }

    public function testChunk() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'chunkSize' => 3,
            'onChunkParsed' => function (array $rows, int $i) {
                if ($i <= 2) {
                    $this->assertEquals(count($rows), 3);
                } else {
                    $this->assertEquals(count($rows), 1);
                }
                if ($i === 1) {
                    $this->assertEquals($rows[0]['A'], 4);
                    $this->assertEquals($rows[0]['B'], 'd');
                }
            },
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                },
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
    }

    public function testChunkOptimizer() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'chunkSize' => 3,
            'onChunkParsed' => function (array $rows, int $i) {
                if ($i === 0) {
                    $this->assertEquals($rows[0]['A'], 'user:1');
                }
            },
            'columns' => [
                'A' => new \voilab\csv\Optimizer(
                    function (string $data) {
                        return (int) $data;
                    },
                    function (array $data) {
                        return array_reduce($data, function ($acc, $value) {
                            $acc[$value] = 'user:' . $value;
                            return $acc;
                        }, []);
                    }
                )
            ]
        ]);
    }
}

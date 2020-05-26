<?php

use PHPUnit\Framework\TestCase;

final class OptimizerTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';'
        ]);
        $this->file = __DIR__ . '/fixtures/csv-optimizer.csv';
    }

    public function testOptimizer() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'strict' => false,
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
                ),
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 'A' => 'user:4', 'B' => 'hello' ],
            [ 'A' => 'user:9', 'B' => 'world' ],
            [ 'A' => 'user:4', 'B' => 'an other' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testOptimizerMissing() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'strict' => false,
            'columns' => [
                'A' => new \voilab\csv\Optimizer(
                    function (string $data) {
                        return (int) $data;
                    },
                    function (array $data) {
                        return [];
                    }
                ),
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $expect = [
            [ 'A' => 4, 'B' => 'hello' ],
            [ 'A' => 9, 'B' => 'world' ],
            [ 'A' => 4, 'B' => 'an other' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testOptimizerThrow() : void
    {
        $this->expectExceptionMessage('data 4 not found');
        $result = $this->parser->fromFile($this->file, [
            'strict' => false,
            'columns' => [
                'A' => new \voilab\csv\Optimizer(
                    function (string $data) {
                        return (int) $data;
                    },
                    function (array $data) {
                        return [];
                    },
                    function (int $data) {
                        throw new \Exception("data $data not found");
                    }
                )
            ]
        ]);
    }

    public function testOptimizerInsideOther() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'strict' => false,
            'columns' => [
                'A' => new \voilab\csv\Optimizer(
                    function (string $data) { return $data; },
                    function (array $data) {
                        return array_reduce($data, function ($acc, $value) {
                            $acc[$value] = 'user:' . $value;
                            return $acc;
                        }, []);
                    }
                ),
                'B' => new \voilab\csv\Optimizer(
                    function (string $data) { return $data; },
                    function (array $data) { return []; },
                    function (string $data, int $index, array $parsed) {
                        return $parsed['A'] . ':b';
                    }
                )
            ]
        ]);
        $expect = [
            [ 'A' => 'user:4', 'B' => 'user:4:b' ],
            [ 'A' => 'user:9', 'B' => 'user:9:b' ],
            [ 'A' => 'user:4', 'B' => 'user:4:b' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

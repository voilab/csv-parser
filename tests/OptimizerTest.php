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
            'strictHeaders' => false,
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
            'strictHeaders' => false,
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
}

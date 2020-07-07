<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;

class Error extends TestCase
{
    protected function setUp() : void
    {
        $this->dir = __DIR__ . '/fixtures';
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';'
        ]);
    }

    protected function tearDown(): void
    {
        $this->resource->close();
    }

    public function testColumnError() : void
    {
        $this->expectExceptionMessage('row');
        $result = $this->parser->parse($this->resource, [
            'strict' => false,
            'onError' => function ($e, $index, $meta) {
                if ($meta['type'] === 'row') {
                    throw new \Exception($meta['type']);
                }
                $this->assertEquals($meta['type'], 'column');
                throw $e;
            },
            'columns' => [
                'A' => function (string $data) {
                    throw new \Exception('error');
                }
            ]
        ]);
    }

    public function testColumnSwallowError() : void
    {
        $result = $this->parser->parse($this->resource, [
            'strict' => false,
            'onError' => function ($e, $index, $meta) {
                return;
            },
            'columns' => [
                'A' => function (string $data) {
                    throw new \Exception('error');
                }
            ]
        ]);
        $this->assertTrue(true);
    }

    public function testOptimizerError() : void
    {
        $this->expectExceptionMessage(4);
        $result = $this->parser->parse($this->resource, [
            'strict' => false,
            'onError' => function ($e, $index, $meta) {
                throw $e;
            },
            'columns' => [
                'A' => new \voilab\csv\Optimizer(
                    function (string $data) {
                        return $data;
                    },
                    function (array $data) {
                        return [];
                    },
                    function ($data) {
                        throw new \Exception($data);
                    }
                )
            ]
        ]);
    }

    public function testOptimizerReduceError() : void
    {
        $this->expectExceptionMessage('reduce');
        $result = $this->parser->parse($this->resource, [
            'strict' => false,
            'onError' => function ($e, $index, $meta) {
                $this->assertNull($index);
                throw $e;
            },
            'columns' => [
                'A' => new \voilab\csv\Optimizer(
                    function (string $data) {
                        return $data;
                    },
                    function (array $data) {
                        throw new \Exception('reduce');
                    },
                    function ($data) {
                        throw new \Exception($data);
                    }
                )
            ]
        ]);
    }

    public function testOptimizerReduceAbsentError() : void
    {
        $this->expectExceptionMessage(4);
        $result = $this->parser->parse($this->resource, [
            'strict' => false,
            'onError' => function ($e, $index, $meta) {
                $this->assertNotNull($index);
                throw $e;
            },
            'columns' => [
                'A' => new \voilab\csv\Optimizer(
                    function (string $data) {
                        return $data;
                    },
                    function (array $data) {
                        return [];
                    },
                    function ($data) {
                        throw new \Exception($data);
                    }
                )
            ]
        ]);
    }
}

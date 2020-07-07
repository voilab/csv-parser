<?php
namespace voilab\csv\test;

use PHPUnit\Framework\TestCase;
use voilab\csv\Exception;

class RequiredHeaders extends TestCase
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

    public function testAbsent() : void
    {
        $this->expectExceptionCode(Exception::HEADERMISSING);
        $result = $this->parser->parse($this->resource, [
            'required' => ['C'],
            'columns' => [
                'A' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testPresent() : void
    {
        $result = $this->parser->parse($this->resource, [
            'required' => ['A'],
            'columns' => [
                'A' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; }
            ]
        ]);
        $expect = [
            [ 'A' => 4, 'C' => '' ],
            [ 'A' => 9, 'C' => '' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testPresentAlias() : void
    {
        $result = $this->parser->parse($this->resource, [
            'required' => ['aliased'],
            'columns' => [
                'A as aliased' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; }
            ]
        ]);
        $expect = [
            [ 'aliased' => 4, 'C' => '' ],
            [ 'aliased' => 9, 'C' => '' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

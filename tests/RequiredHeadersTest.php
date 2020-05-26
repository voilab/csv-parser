<?php

use PHPUnit\Framework\TestCase;
use voilab\csv\Exception;

final class RequiredHeadersTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strict' => false
        ]);
        $this->file = __DIR__ . '/fixtures/csv-required-headers.csv';
    }

    public function testAbsent() : void
    {
        $this->expectExceptionCode(Exception::HEADERMISSING);
        $result = $this->parser->fromFile($this->file, [
            'required' => ['C'],
            'columns' => [
                'A' => function (string $data) { return $data; },
                'C' => function (string $data) { return $data; }
            ]
        ]);
    }

    public function testPresent() : void
    {
        $result = $this->parser->fromFile($this->file, [
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
        $result = $this->parser->fromFile($this->file, [
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

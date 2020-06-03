<?php
namespace voilab\csv\test\iterable;

use PHPUnit\Framework\TestCase;

final class FromTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'columns' => [
                'A' => function (string $data) {
                    return (int) $data;
                },
                'B' => function (string $data) {
                    return $data;
                }
            ]
        ]);
        $this->content = require __DIR__ . '/../fixtures/csv-array.php';
    }

    public function testArray() : void
    {
        $result = $this->parser->fromIterable($this->content);

        $expect = [
            [ 'A' => 4, 'B' => 'hello' ],
            [ 'A' => 9, 'B' => 'world' ]
        ];
        $this->assertEquals($result, $expect);
    }
}

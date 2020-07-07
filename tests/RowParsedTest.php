<?php

use PHPUnit\Framework\TestCase;

final class RowParsedTest extends TestCase
{
    protected function setUp() : void
    {
        $this->parser = new \voilab\csv\Parser([
            'delimiter' => ';',
            'strict' => false
        ]);
        $this->file = __DIR__ . '/fixtures/csv-parser.csv';
    }

    public function testNewColumnInFn() : void
    {
        $result = $this->parser->fromFile($this->file, [
            'onRowParsed' => function ($row) {
                $row['new column'] = 1;
                return $row;
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
        $expect = [
            [ 'A' => 'user:4', 'new column' => 1 ],
            [ 'A' => 'user:9', 'new column' => 1 ]
        ];
        $this->assertEquals($result, $expect);
    }
}

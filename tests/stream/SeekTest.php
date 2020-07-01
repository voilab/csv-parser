<?php
namespace voilab\csv\test\stream;

class SeekTest extends \voilab\csv\test\Seek
{
    use TraitResource;

    protected function setUp() : void
    {
        parent::setUp();
        $this->resource = $this->getResource('csv-seek.csv');
    }

    public function testSeekWithStartUnsync() : void
    {
        $result = $this->parser->parse($this->resource, [
            'start' => 0,
            'size' => 3
        ]);
        $result = $this->parser->parse($this->resource, [
            'size' => 3,
            'seek' => $this->resource->tell()
        ]);
        $expect = [
            [ 'A' => 4, 'B' => 'test 4' ],
            [ 'A' => 5, 'B' => 'test 5' ],
            [ 'A' => 6, 'B' => 'test 6' ]
        ];
        $this->assertEquals($result, $expect);
    }

    public function testSeekPointerOverflow() : void
    {
        $result = $this->parser->parse($this->resource, [
            'start' => 0,
            'size' => 3
        ]);

        $this->expectException('RuntimeException');
        $result = $this->parser->parse($this->resource, [
            'size' => 3,
            'seek' => 500000
        ]);

        $expect = [];
        $this->assertEquals($result, $expect);
    }
}
